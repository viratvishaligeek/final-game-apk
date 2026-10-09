<?php

namespace App\Services;

use App\Models\Bid;
use App\Models\Game;
use App\Models\Page;
use App\Models\User;
use App\Models\WalletRequest;
use App\Models\Winner;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardService
{
    public function getDashboardData(Request $request): array
    {
        $today = today();
        $period = $request->input('period', '7days');
        $allowedPeriods = ['7days', '30days', 'month', '12months'];

        if (!in_array($period, $allowedPeriods, true)) {
            $period = '7days';
        }

        $totalUsers = User::query()
            ->where('status', 'active')
            ->count();

        $totalGames = Game::query()
            ->where('status', 'active')
            ->count();

        $todayTotalBids = Bid::query()
            ->whereDate('game_date', $today)
            ->count();

        $todayBidAmount = Bid::query()
            ->whereDate('game_date', $today)
            ->sum('amount');

        $totalPages = Page::query()
            ->where('status', 'active')
            ->count();

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
            ->where('status', 'pending')
            ->sum('amount');

        $todayWinnerCount = Winner::query()
            ->whereDate('game_date', $today)
            ->count();

        $todayWinningAmount = Winner::query()
            ->whereDate('game_date', $today)
            ->sum('winning_amount');

        $todayWinners = Winner::query()
            ->with([
                'user:id,name,phone',
                'game:id,name',
                'bid:id,amount,type,number',
            ])
            ->whereDate('game_date', $today)
            ->latest('id')
            ->limit(50)
            ->get();

        [$chartStart, $chartEnd, $groupFormat] =
            $this->getChartRange($period);

        $withdrawChart = $this->getWalletChartData(
            'debit',
            $chartStart,
            $chartEnd,
            $groupFormat
        );

        $moneyAddedChart = $this->getWalletChartData(
            'credit',
            $chartStart,
            $chartEnd,
            $groupFormat
        );

        $chartLabels = $this->getChartLabels(
            $chartStart,
            $chartEnd,
            $period
        );

        $withdrawValues = $this->mapChartValues(
            $chartLabels,
            $withdrawChart,
            $period
        );

        $moneyAddedValues = $this->mapChartValues(
            $chartLabels,
            $moneyAddedChart,
            $period
        );

        return compact(
            'totalUsers',
            'totalGames',
            'todayTotalBids',
            'todayBidAmount',
            'totalPages',
            'pendingWithdraw',
            'completeWithdraw',
            'pendingAddMoney',
            'todayWinnerCount',
            'todayWinningAmount',
            'todayWinners',
            'period',
            'chartLabels',
            'withdrawValues',
            'moneyAddedValues'
        );
    }

    private function getChartRange(string $period): array
    {
        $today = today();

        return match ($period) {
            '30days' => [
                $today->copy()->subDays(29)->startOfDay(),
                $today->copy()->endOfDay(),
                'day',
            ],
            'month' => [
                $today->copy()->startOfMonth()->startOfDay(),
                $today->copy()->endOfDay(),
                'day',
            ],
            '12months' => [
                $today->copy()->subMonths(11)->startOfMonth()->startOfDay(),
                $today->copy()->endOfMonth()->endOfDay(),
                'month',
            ],
            default => [
                $today->copy()->subDays(6)->startOfDay(),
                $today->copy()->endOfDay(),
                'day',
            ],
        };
    }

    private function getWalletChartData(
        string $requestType,
        Carbon $start,
        Carbon $end,
        string $groupFormat
    ) {
        $query = WalletRequest::query()
            ->where('request_type', $requestType)
            ->where('status', 'approved')
            ->whereBetween('processed_at', [$start, $end]);

        if ($groupFormat === 'month') {
            return $query
                ->selectRaw("
                    DATE_FORMAT(processed_at, '%Y-%m') as period,
                    SUM(amount) as total
                ")
                ->groupBy('period')
                ->orderBy('period')
                ->pluck('total', 'period');
        }

        return $query
            ->selectRaw("
                DATE(processed_at) as period,
                SUM(amount) as total
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period');
    }

    private function getChartLabels(
        Carbon $start,
        Carbon $end,
        string $period
    ): array {
        $labels = [];

        if ($period === '12months') {
            $cursor = $start->copy()->startOfMonth();

            while ($cursor->lte($end)) {
                $labels[] = $cursor->format('Y-m');
                $cursor->addMonth();
            }

            return $labels;
        }

        $cursor = $start->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $labels[] = $cursor->format('Y-m-d');
            $cursor->addDay();
        }

        return $labels;
    }

    private function mapChartValues(
        array $labels,
        $data,
        string $period
    ): array {
        return collect($labels)
            ->map(function ($label) use ($data) {
                return round((float) ($data[$label] ?? 0), 2);
            })
            ->values()
            ->all();
    }
}
