<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function credit(
        User $user,
        float $amount,
        ?string $remark = null
    ): Transaction {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Amount must be greater than zero.',
            ]);
        }

        return DB::transaction(function () use ($user, $amount, $remark) {
            $user = User::query()
                ->lockForUpdate()
                ->findOrFail($user->id);

            $oldBalance = (float) $user->balance;
            $newBalance = round($oldBalance + $amount, 2);

            $user->update([
                'balance' => $newBalance,
            ]);

            $transaction = Transaction::create([
                'phone' => $user->phone,
                'user_id' => $user->id,
                'amount' => $amount,
                'subject' => $remark ?: 'Wallet credited',
                'balance' => $newBalance,
                'status' => 'completed',
                'type' => 'credit',
            ]);

            $this->notificationService->create(
                $user,
                'Wallet Credited',
                '₹' . number_format($amount, 2)
                    . ' has been credited to your wallet. '
                    . 'Available balance: ₹'
                    . number_format($newBalance, 2) . '.'
            );

            return $transaction;
        });
    }

    public function debit(
        User $user,
        float $amount,
        ?string $remark = null
    ): Transaction {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Amount must be greater than zero.',
            ]);
        }

        return DB::transaction(function () use ($user, $amount, $remark) {
            $user = User::query()
                ->lockForUpdate()
                ->findOrFail($user->id);

            $oldBalance = (float) $user->balance;

            if ($amount > $oldBalance) {
                throw ValidationException::withMessages([
                    'amount' => 'Insufficient wallet balance.',
                ]);
            }

            $newBalance = round($oldBalance - $amount, 2);

            $user->update([
                'balance' => $newBalance,
            ]);

            $transaction = Transaction::create([
                'phone' => $user->phone,
                'user_id' => $user->id,
                'amount' => $amount,
                'subject' => $remark ?: 'Wallet debited',
                'balance' => $newBalance,
                'status' => 'completed',
                'type' => 'debit',
            ]);

            $this->notificationService->create(
                $user,
                'Wallet Debited',
                '₹' . number_format($amount, 2)
                    . ' has been debited from your wallet. '
                    . 'Available balance: ₹' . number_format($newBalance, 2) . '.'
            );

            return $transaction;
        });
    }
}
