<?php

namespace App\Services\Admin;

use App\Models\Bid;
use App\Models\Game;
use App\Models\Transaction;
use App\Models\WalletRequest;
use App\Models\Winner;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BiddingDeskProfitLossReportService
{
    /**
     * Build the date-scoped wallet and game P&L report data.
     *
     * All model queries retain their existing site/global scopes. Wallet
     * request totals and ledger totals stay separate to avoid double counting.
     */
    public function build(string $from, string $to): array
    {
        $startDate = Carbon::parse($from)->toDateString();
        $endDate = Carbon::parse($to)->toDateString();
        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime = $endDate . ' 23:59:59';

        $totalUser = WalletRequest::where('request_type', 'credit')
            ->where('status', 'approved')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->sum('amount');

        $totalMatch = WalletRequest::where('request_type', 'debit')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->sum('amount');

        $totalTrans = WalletRequest::where('request_type', 'debit')
            ->where('status', 'approved')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->sum('amount');

        $successfulStatuses = ['completed', 'success', 'successful'];
        $adminCreditTotal = Transaction::query()
            ->whereNotNull('admin_id')
            ->where('type', 'credit')
            ->whereIn('status', $successfulStatuses)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->sum('amount');
        $adminDebitTotal = Transaction::query()
            ->whereNotNull('admin_id')
            ->where('type', 'debit')
            ->whereIn('status', $successfulStatuses)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->sum('amount');

        $userCreditTotal = Transaction::query()
            ->whereNull('admin_id')
            ->where('type', 'credit')
            ->whereIn('status', $successfulStatuses)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->sum('amount');
        $userDebitTotal = Transaction::query()
            ->whereNull('admin_id')
            ->where('type', 'debit')
            ->whereIn('status', $successfulStatuses)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->sum('amount');

        $games = Game::where('status', 'active')->orderBy('serial', 'asc')->get();
        $bidTotals = Bid::query()
            ->select('game_id', DB::raw('SUM(amount) as total_bid'))
            ->whereBetween('game_date', [$startDate, $endDate])
            ->groupBy('game_id')
            ->pluck('total_bid', 'game_id');
        $winTotals = Winner::query()
            ->select('game_id', DB::raw('SUM(winning_amount) as total_win'))
            ->whereBetween('game_date', [$startDate, $endDate])
            ->groupBy('game_id')
            ->pluck('total_win', 'game_id');

        $gameStats = $games->map(function ($game) use ($bidTotals, $winTotals) {
            $totalBid = (float) ($bidTotals->get($game->id) ?? 0);
            $totalWin = (float) ($winTotals->get($game->id) ?? 0);

            return [
                'id' => $game->id,
                'name' => $game->name,
                'total_bid' => $totalBid,
                'total_win' => $totalWin,
                'profit_loss' => $totalBid - $totalWin,
            ];
        });

        $grandTotalBid = $gameStats->sum('total_bid');
        $grandTotalWin = $gameStats->sum('total_win');

        return [
            'gameStats' => $gameStats,
            'totalUser' => $totalUser,
            'totalMatch' => $totalMatch,
            'totalTrans' => $totalTrans,
            'adminCreditTotal' => $adminCreditTotal,
            'adminDebitTotal' => $adminDebitTotal,
            'userCreditTotal' => $userCreditTotal,
            'userDebitTotal' => $userDebitTotal,
            'grandTotalBid' => $grandTotalBid,
            'grandTotalWin' => $grandTotalWin,
            'grandProfitLoss' => $grandTotalBid - $grandTotalWin,
            'from' => $from,
            'to' => $to,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }
}
