<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    public function __construct(protected NotificationService $notificationService) {}
    public function credit(User $user, float $amount, ?string $remark = null, array $metadata = []): Transaction
    {
        $this->validateAmount($amount);

        return DB::transaction(function () use ($user, $amount, $remark, $metadata) {
            $idempotencyKey = $metadata['idempotency_key'] ?? null;

            if ($idempotencyKey) {
                $existing = Transaction::query()
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existing) {
                    return $existing;
                }
            }

            $user = User::query()->lockForUpdate()->findOrFail($user->id);
            $previousBalance = round((float) $user->balance, 2);
            $newBalance = round($previousBalance + $amount, 2);

            $user->update(['balance' => $newBalance]);

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'previous_balance' => $previousBalance,
                'balance' => $newBalance,
                'subject' => $remark ?: 'Wallet credited',
                'status' => 'completed',
                'type' => 'credit',
                'admin_id' => $metadata['admin_id'] ?? null,
                'idempotency_key' => $idempotencyKey,
            ]);

            $this->notificationService->create(
                $user,
                'Wallet Credited',
                '₹' . number_format($amount, 2) . ' has been credited to your wallet. Available balance: ₹' . number_format($newBalance, 2) . '.'
            );

            return $transaction;
        });
    }

    public function debit(User $user, float $amount, ?string $remark = null, bool $notifyUser = true): Transaction
    {
        $this->validateAmount($amount);
        return DB::transaction(function () use ($user, $amount, $remark, $notifyUser) {
            $user = User::query()->lockForUpdate()->findOrFail($user->id);
            $currentBalance = (float) $user->balance;
            if ($amount > $currentBalance) {
                throw ValidationException::withMessages(['amount' => 'Insufficient wallet balance.',]);
            }
            $newBalance = round($currentBalance - $amount, 2);
            $user->update(['balance' => $newBalance,]);
            $transaction = Transaction::create(['user_id' => $user->id, 'amount' => $amount, 'balance' => $newBalance, 'subject' => $remark ?: 'Wallet debited', 'status' => 'completed', 'type' => 'debit',]);
            if ($notifyUser) {
                $this->notificationService->create($user, 'Wallet Debited', '₹' . number_format($amount, 2) . ' has been debited from your wallet. ' . 'Available balance: ₹' . number_format($newBalance, 2) . '.');
            }
            return $transaction;
        });
    }
    private function validateAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'Amount must be greater than zero.',]);
        }
    }
}
