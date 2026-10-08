<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Game;
use App\Models\User;
use App\Models\Bid;
use App\Models\Result;
use App\Models\Winner;
use App\Models\Transaction;
use App\Models\WalletRequest;
use App\Models\Notification;

class SeedDemoData extends Command
{
    protected $signature = 'app:seed-demo-data
                            {--months=3 : Number of months of demo data}
                            {--fresh : Remove existing demo data first}';

    protected $description = 'Generate realistic demo data for games, results, bids, winners, transactions and wallet requests';

    public function handle(): int
    {
        $months = max(1, (int) $this->option('months'));

        $this->info("Generating {$months} months of demo data...");

        DB::transaction(function () use ($months) {
            $games = Game::query()
                ->where('status', 'active')
                ->orderBy('serial')
                ->get();

            $users = User::query()
                ->where('status', 'active')
                ->get();

            if ($games->isEmpty()) {
                $this->error('No active games found.');
                return;
            }

            if ($users->isEmpty()) {
                $this->error('No active users found.');
                return;
            }

            if ($this->option('fresh')) {
                $this->clearDemoData();
            }

            $this->seedResults($games, $months);
            $this->seedBids($games, $users, $months);
            $this->seedWalletRequests($users, $months);
            $this->seedNotifications($users);

            $this->syncBalances($users);

            $this->info('Demo data generated successfully.');
        });

        return self::SUCCESS;
    }

    private function seedResults($games, int $months): void
    {
        $startDate = now()
            ->subMonths($months)
            ->startOfDay();

        $endDate = now()->startOfDay();

        foreach ($games as $game) {
            for (
                $date = $startDate->copy();
                $date->lte($endDate);
                $date->addDay()
            ) {
                $jodi = str_pad((string) random_int(0, 99), 2, '0', STR_PAD_LEFT);

                Result::updateOrCreate(
                    [
                        'game_id' => $game->id,
                        'game_date' => $date->toDateString(),
                        'type' => 'ah',
                    ],
                    [
                        'number' => $jodi[0],
                    ]
                );

                Result::updateOrCreate(
                    [
                        'game_id' => $game->id,
                        'game_date' => $date->toDateString(),
                        'type' => 'bh',
                    ],
                    [
                        'number' => $jodi[1],
                    ]
                );

                Result::updateOrCreate(
                    [
                        'game_id' => $game->id,
                        'game_date' => $date->toDateString(),
                        'type' => 'jodi',
                    ],
                    [
                        'number' => $jodi,
                    ]
                );

                if ($date->isSameDay($endDate)) {
                    $game->update([
                        'last_result' => $jodi,
                    ]);
                }
            }
        }

        $this->line('Results generated.');
    }

    private function seedBids($games, $users, int $months): void
    {
        $startDate = now()
            ->subMonths($months)
            ->startOfDay();

        $endDate = now()->startOfDay();

        foreach ($games as $game) {
            for (
                $date = $startDate->copy();
                $date->lte($endDate);
                $date->addDay()
            ) {
                $result = Result::query()
                    ->where('game_id', $game->id)
                    ->where('game_date', $date->toDateString())
                    ->where('type', 'jodi')
                    ->value('number');

                if (!$result) {
                    continue;
                }

                $usersForDay = $users->shuffle()->take(
                    min(5, $users->count())
                );

                foreach ($usersForDay as $user) {
                    $amount = random_int(10, 500);

                    $isJodi = random_int(1, 100) <= 35;

                    if ($isJodi) {
                        $type = 'jodi';
                        $number = random_int(1, 100) <= 20
                            ? $result
                            : str_pad(
                                (string) random_int(0, 99),
                                2,
                                '0',
                                STR_PAD_LEFT
                            );
                    } else {
                        $type = random_int(1, 2) === 1
                            ? 'haruf'
                            : 'cross';

                        $number = (string) random_int(0, 99);
                    }

                    $status = 'loss';
                    $winningAmount = 0;

                    if (
                        $type === 'jodi' &&
                        $number === $result
                    ) {
                        $status = 'win';

                        $winningAmount = round(
                            $amount * (float) $game->reward,
                            2
                        );
                    }

                    $bid = Bid::create([
                        'order_no' => 'DEMO-' . strtoupper(
                            uniqid()
                        ),
                        'user_id' => $user->id,
                        'game_id' => $game->id,
                        'game_date' => $date->toDateString(),
                        'type' => $type,
                        'number' => $number,
                        'amount' => $amount,
                        'status' => $status,
                        'winning_amount' => $winningAmount,
                    ]);

                    if ($status === 'win') {
                        $winner = Winner::create([
                            'bid_id' => $bid->id,
                            'user_id' => $user->id,
                            'game_id' => $game->id,
                            'number' => $number,
                            'type' => $type,
                            'game_date' => $date->toDateString(),
                            'amount' => $amount,
                            'winning_amount' => $winningAmount,
                        ]);

                        Transaction::create([
                            'user_id' => $user->id,
                            'amount' => $winningAmount,
                            'balance' => 0,
                            'subject' => 'Demo Winning - ' . $game->name,
                            'type' => 'credit',
                            'status' => 'completed',
                            'game_id' => $game->id,
                            'bid_id' => $bid->id,
                        ]);
                    }
                }
            }
        }

        $this->line('Bids and winners generated.');
    }

    private function seedWalletRequests($users, int $months): void
    {
        foreach ($users as $user) {
            WalletRequest::create([
                'user_id' => $user->id,
                'request_type' => 'credit',
                'payment_method' => 'upi',
                'amount' => random_int(500, 5000),
                'status' => 'approved',
                'utr' => 'DEMO' . random_int(10000000, 99999999),
                'client_txn_id' => 'DEMO-CL-' . uniqid(),
                'gateway_txn_id' => 'DEMO-GW-' . uniqid(),
                'customer_vpa' => 'demo@upi',
                'gateway_status' => 'success',
                'remark' => 'Demo wallet recharge',
                'processed_at' => now()->subDays(random_int(1, $months * 30)),
            ]);
        }

        $this->line('Wallet requests generated.');
    }

    private function seedNotifications($users): void
    {
        foreach ($users->take(10) as $user) {
            Notification::create([
                'user_id' => $user->id,
                'subject' => 'Demo Notification',
                'message' => 'This is a demo notification generated for testing.',
            ]);
        }

        Notification::create([
            'user_id' => null,
            'subject' => 'Demo Result Update',
            'message' => 'Demo game result has been published.',
        ]);

        $this->line('Notifications generated.');
    }

    private function syncBalances($users): void
    {
        foreach ($users as $user) {
            $credit = Transaction::query()
                ->where('user_id', $user->id)
                ->where('type', 'credit')
                ->where('status', 'completed')
                ->sum('amount');

            $debit = Transaction::query()
                ->where('user_id', $user->id)
                ->where('type', 'debit')
                ->where('status', 'completed')
                ->sum('amount');

            $balance = max(
                0,
                round($credit - $debit, 2)
            );

            $user->update([
                'balance' => $balance,
            ]);

            Transaction::query()
                ->where('user_id', $user->id)
                ->orderBy('id')
                ->get()
                ->each(function ($transaction) use (&$balance) {
                    if ($transaction->type === 'credit') {
                        $balance += (float) $transaction->amount;
                    } else {
                        $balance -= (float) $transaction->amount;
                    }

                    $transaction->update([
                        'balance' => round($balance, 2),
                    ]);
                });
        }

        $this->line('User balances synchronized.');
    }

    private function clearDemoData(): void
    {
        Winner::query()->delete();
        Bid::query()->delete();
        Result::query()->delete();

        Transaction::query()
            ->where('subject', 'like', 'Demo%')
            ->delete();

        WalletRequest::query()
            ->where('client_txn_id', 'like', 'DEMO-%')
            ->delete();

        Notification::query()
            ->where('subject', 'like', 'Demo%')
            ->delete();

        $this->warn('Existing demo records cleared.');
    }
}
