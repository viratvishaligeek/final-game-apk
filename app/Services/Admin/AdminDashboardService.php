<?php

namespace App\Services\Admin;

use App\Models\Bid;
use App\Models\Game;
use App\Models\Page;
use App\Models\User;
use App\Models\WalletRequest;
use App\Models\Winner;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class AdminDashboardService
{
    public function build(string $period = '7days'): array
    {
        $allowedPeriods = ['7days', '30days', 'month', '12months'];
        if (!in_array($period, $allowedPeriods, true)) {
            $period = '7days';
        }

        $now = now()->timezone(config('app.timezone'));
        $gameDates = Game::query()
            ->get(['id', 'name', 'slug', 'result_time'])
            ->mapWithKeys(fn (Game $game) => [$game->id => $game->businessDate($now)])
            ->all();

        $totalUsers = User::query()->where('status', 'active')->count();
        $totalGames = Game::query()->where('status', 'active')->count();
        $todayTotalBids = $this->whereGameBusinessDates(Bid::query(), $gameDates)->count();
        $todayBidAmount = $this->whereGameBusinessDates(Bid::query(), $gameDates)->sum('amount');
        $totalPages = Page::query()->where('status', 'active')->count();

        $pendingWithdraw = WalletRequest::query()
            ->where('request_type', 'debit')
            ->where('status', 'pending')
            ->sum('amount');
        $completeWithdraw = WalletRequest::query()
            ->where('request_type', 'debit')
            ->where('status', 'approved')
            ->sum('amount');
        $pendingAddMoney = WalletRequest::query()
            ->where('request_type', 'credit')
            ->whereIn('status', ['pending', 'processing'])
            ->sum('amount');

        $todayWinnerCount = $this->whereGameBusinessDates(Winner::query(), $gameDates)->count();
        $todayWinningAmount = $this->whereGameBusinessDates(Winner::query(), $gameDates)->sum('winning_amount');
        $todayWinners = $this->whereGameBusinessDates(
            Winner::query()->with([
                'user:id,name,phone',
                'game:id,name',
                'bid:id,amount,type,number',
            ]),
            $gameDates
        )->latest('id')->limit(50)->get();

        return [
            'period' => $period,
            'totalUsers' => $totalUsers,
            'totalGames' => $totalGames,
            'todayTotalBids' => $todayTotalBids,
            'todayBidAmount' => $todayBidAmount,
            'totalPages' => $totalPages,
            'pendingWithdraw' => $pendingWithdraw,
            'completeWithdraw' => $completeWithdraw,
            'pendingAddMoney' => $pendingAddMoney,
            'todayWinnerCount' => $todayWinnerCount,
            'todayWinningAmount' => $todayWinningAmount,
            'todayWinners' => $todayWinners,
            'totalUserTable' => $this->getTotalUserReport(),
        ];
    }

    private function whereGameBusinessDates(Builder $query, array $gameDates): Builder
    {
        return $query->where(function (Builder $outer) use ($gameDates) {
            if ($gameDates === []) {
                $outer->whereRaw('1 = 0');
                return;
            }

            foreach ($gameDates as $gameId => $date) {
                $outer->orWhere(function (Builder $perGame) use ($gameId, $date) {
                    $perGame->where('game_id', $gameId)->where('game_date', $date);
                });
            }
        });
    }

    private function getTotalUserReport(): array
    {
        $users = User::query()->select('id', 'name')->orderBy('name')->get();
        if ($users->isEmpty()) return [];

        $userIds = $users->pluck('id');
        $totalAdded = WalletRequest::query()
            ->whereIn('user_id', $userIds)
            ->where('request_type', 'credit')
            ->where('status', 'approved')
            ->selectRaw('user_id, COALESCE(SUM(amount), 0) AS total_added')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $betStats = Bid::query()
            ->whereIn('user_id', $userIds)
            ->selectRaw('user_id, COALESCE(SUM(amount), 0) AS total_bet, COALESCE(SUM(winning_amount), 0) AS total_win, MIN(game_date) AS first_played, MAX(game_date) AS last_played')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $totalWithdrawals = WalletRequest::query()
            ->whereIn('user_id', $userIds)
            ->where('request_type', 'debit')
            ->where('status', 'approved')
            ->selectRaw('user_id, COALESCE(SUM(amount), 0) AS total_withdrawal')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        return $users->map(function ($user) use ($totalAdded, $betStats, $totalWithdrawals) {
            $added = $totalAdded->get($user->id);
            $bets = $betStats->get($user->id);
            $withdrawal = $totalWithdrawals->get($user->id);
            $firstPlayed = $bets?->first_played;
            $lastPlayed = $bets?->last_played;
            $playingPeriod = ($firstPlayed && $lastPlayed)
                ? Carbon::parse($firstPlayed)->format('d M Y') . ' - ' . Carbon::parse($lastPlayed)->format('d M Y')
                : 'Not Played';

            return [
                'user_name' => $user->name,
                'total_added' => round((float) ($added?->total_added ?? 0), 2),
                'total_bet' => round((float) ($bets?->total_bet ?? 0), 2),
                'total_win' => round((float) ($bets?->total_win ?? 0), 2),
                'total_withdrawal' => round((float) ($withdrawal?->total_withdrawal ?? 0), 2),
                'playing_period' => $playingPeriod,
            ];
        })->values()->all();
    }
}
