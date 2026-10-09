<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Game;
use App\Models\Page;
use App\Models\User;
use App\Models\WalletRequest;
use App\Models\Winner;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $today = today();
        $gameDates = Game::query()
            ->get(['id', 'name', 'slug', 'result_time'])
            ->mapWithKeys(fn (Game $game) => [$game->id => $game->businessDate()])
            ->all();

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

        $todayTotalBids = $this->whereGameBusinessDates(
            Bid::query(),
            $gameDates
        )->count();

        $todayBidAmount = $this->whereGameBusinessDates(
            Bid::query(),
            $gameDates
        )->sum('amount');

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
            ->whereIn('status', ['pending', 'processing'])
            ->sum('amount');

        $todayWinnerCount = $this->whereGameBusinessDates(
            Winner::query(),
            $gameDates
        )->count();

        $todayWinningAmount = $this->whereGameBusinessDates(
            Winner::query(),
            $gameDates
        )->sum('winning_amount');

        $todayWinners = $this->whereGameBusinessDates(
            Winner::query()->with([
                'user:id,name,phone',
                'game:id,name',
                'bid:id,amount,type,number',
            ]),
            $gameDates
        )
            ->latest('id')
            ->limit(50)
            ->get();

        [$chartStart, $chartEnd, $groupFormat] = $this->getChartRange($period);

        $withdrawChart = $this->getWalletChartData(
            requestType: 'debit',
            start: $chartStart,
            end: $chartEnd,
            groupFormat: $groupFormat
        );

        $moneyAddedChart = $this->getWalletChartData(
            requestType: 'credit',
            start: $chartStart,
            end: $chartEnd,
            groupFormat: $groupFormat
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

        return view('backend.dashboard', [
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

            'period' => $period,
            'chartLabels' => $chartLabels,
            'withdrawValues' => $withdrawValues,
            'moneyAddedValues' => $moneyAddedValues,
        ]);
    }

    /**
     * Get chart date range.
     */
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

    /**
     * Get approved wallet request chart data.
     */
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

    /**
     * Generate chart labels.
     */
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

    /**
     * Map DB chart values to all labels.
     */
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

    public function profile()
    {
        $admin = auth('admin')->user();
        $pageName = 'Admin Profile Settings';
        return view('backend.profile', compact('admin', 'pageName'));
    }

    /**
     * Update basic profile information.
     */
    public function update(Request $request)
    {
        $admin = auth('admin')->user();
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin->id)],
        ]);
        $admin->update($validated);
        return back()->with('success', 'Profile details updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $admin = auth('admin')->user();
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], $admin->password)) {
            return back()->with('error', 'The provided current password does not match our records.');
        }
        $admin->update([
            'password' => Hash::make($validated['password']),
        ]);
        return back()->with('success', 'Password updated successfully.');
    }

    /**
     * Apply each game's own business date to dashboard totals.
     */
    private function whereGameBusinessDates(Builder $query, array $gameDates): Builder
    {
        return $query->where(function (Builder $outer) use ($gameDates) {
            if ($gameDates === []) {
                $outer->whereRaw('1 = 0');
                return;
            }

            foreach ($gameDates as $gameId => $date) {
                $outer->orWhere(function (Builder $perGame) use ($gameId, $date) {
                    $perGame->where('game_id', $gameId)
                        ->where('game_date', $date);
                });
            }
        });
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}
