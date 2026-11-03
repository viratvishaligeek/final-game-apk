<?php

namespace App\Services\Admin;

use App\Models\Transaction;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Throwable;

class AdminWalletCreditService
{
    public function __construct(private WalletService $walletService) {}

    /**
     * Credit an Admin-selected account once per idempotency key.
     *
     * The wallet service owns the balance transaction/locking. This service
     * owns replay detection and resolves a concurrent unique-key race by
     * reading the committed transaction after the competing request finishes.
     *
     * @return array{transaction: Transaction, duplicate: bool}
     */
    public function credit(
        User $user,
        float $amount,
        ?string $remark,
        ?string $idempotencyKey,
        ?int $adminId
    ): array {
        if ($idempotencyKey) {
            $existing = Transaction::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                return [
                    'transaction' => $this->assertCompatible($existing, $user, $amount),
                    'duplicate' => true,
                ];
            }
        }

        try {
            $transaction = $this->walletService->credit(
                $user,
                $amount,
                $remark,
                [
                    'admin_id' => $adminId,
                    'idempotency_key' => $idempotencyKey,
                ]
            );

            return [
                'transaction' => $this->assertCompatible($transaction, $user, $amount),
                'duplicate' => false,
            ];
        } catch (Throwable $exception) {
            if ($idempotencyKey) {
                $existing = Transaction::query()
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existing) {
                    return [
                        'transaction' => $this->assertCompatible($existing, $user, $amount),
                        'duplicate' => true,
                    ];
                }
            }

            throw $exception;
        }
    }

    private function assertCompatible(Transaction $transaction, User $user, float $amount): Transaction
    {
        if (
            (int) $transaction->user_id !== (int) $user->id
            || round((float) $transaction->amount, 2) !== round($amount, 2)
            || $transaction->type !== 'credit'
        ) {
            throw new ConflictHttpException(
                'This submission token has already been used for a different credit.'
            );
        }

        return $transaction;
    }
}
