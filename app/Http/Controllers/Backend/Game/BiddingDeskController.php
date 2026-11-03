<?php

namespace App\Http\Controllers\Backend\Game;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Game;
use App\Models\WalletRequest;
use App\Models\Winner;
use App\Models\Transaction;
use App\Services\BidAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BiddingDeskController extends Controller
{
    public function index()
    {
        $games = Game::query()
            ->orderBy('name')
            ->get(['id', 'name']);
        return view('backend.bidding-desk', compact('games'));
    }

    public function __construct(private readonly BidAnalyticsService $bidAnalytics)
    {
    }

    public function data(Request $request)
    {
        $validated = $request->validate([
            'game_id' => ['required', 'integer', 'exists:games,id'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $game = Game::query()->findOrFail($validated['game_id']);
        $date = $validated['date'] ?? $game->businessDate();

        return response()->json([
            'success' => true,
            'data' => $this->bidAnalytics->distribution($game, $date),
        ]);
    }

    public function details(Request $request)
    {
        $validated = $request->validate([
            'game_id' => ['required', 'integer', 'exists:games,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'number' => ['required', 'string', 'max:30'],
            'type' => ['nullable', 'in:jodi,haruf,cross'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $type = $validated['type'] ?? (
            str_starts_with($validated['number'], 'ander-')
            || str_starts_with($validated['number'], 'bahar-')
                ? 'haruf'
                : 'jodi'
        );

        return response()->json([
            'success' => true,
            'data' => $this->bidAnalytics->numberDetails($validated, $type),
        ]);
    }

    // profitLoss
    public function profitLoss(Request $request)
    {
        $from = $request->get('from', Carbon::today()->toDateString());
        $to = $request->get('to', Carbon::today()->toDateString());
        $startDate = Carbon::parse($from)->toDateString();
        $endDate = Carbon::parse($to)->toDateString();
        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime   = $endDate . ' 23:59:59';
        $totalUser = WalletRequest::where('request_type', 'credit')
            ->where('status', 'approved')
            ->whereBetween('created_at', [
                $startDateTime,
                $endDateTime
            ])
            ->sum('amount');
        $totalMatch = WalletRequest::where('request_type', 'debit')
            ->whereBetween('created_at', [
                $startDateTime,
                $endDateTime
            ])
            ->sum('amount');

        $totalTrans = WalletRequest::where('request_type', 'debit')
            ->where('status', 'approved')
            ->whereBetween('created_at', [
                $startDateTime,
                $endDateTime
            ])
            ->sum('amount');

        // Direct admin wallet adjustments are kept separate from approved
        // Add Money / withdrawal requests to prevent duplicate accounting.
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

        // Non-admin wallet ledger movements are shown separately from request
        // summaries and game profit so they are not added twice to net P&L.
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

        $games = Game::where('status', 'active')
            ->orderBy('serial', 'asc')
            ->get();

        $bidTotals = Bid::query()
            ->select(
                'game_id',
                DB::raw('SUM(amount) as total_bid')
            )
            ->whereBetween('game_date', [$startDate, $endDate])
            ->groupBy('game_id')
            ->pluck('total_bid', 'game_id');
        $winTotals = Winner::query()
            ->select(
                'game_id',
                DB::raw('SUM(winning_amount) as total_win')
            )
            ->whereBetween('game_date', [$startDate, $endDate])
            ->groupBy('game_id')
            ->pluck('total_win', 'game_id');
        $gameStats = $games->map(function ($game) use ($bidTotals, $winTotals) {
            $totalBid = (float) ($bidTotals->get($game->id) ?? 0);
            $totalWin = (float) ($winTotals->get($game->id) ?? 0);
            $profitLoss = $totalBid - $totalWin;
            return [
                'id'          => $game->id,
                'name'        => $game->name,
                'total_bid'   => $totalBid,
                'total_win'   => $totalWin,
                'profit_loss' => $profitLoss,
            ];
        });

        $grandTotalBid = $gameStats->sum('total_bid');
        $grandTotalWin = $gameStats->sum('total_win');
        $grandProfitLoss = $grandTotalBid - $grandTotalWin;
        return view('backend.profit_loss_history', compact(
            'gameStats',
            'totalUser',
            'totalMatch',
            'totalTrans',
            'adminCreditTotal',
            'adminDebitTotal',
            'userCreditTotal',
            'userDebitTotal',
            'grandTotalBid',
            'grandTotalWin',
            'grandProfitLoss',
            'from',
            'to',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Inspect completed direct admin wallet adjustments included in the P&L summary.
     */
    public function profitLossTransactions(Request $request)
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'type' => ['required', 'in:credit,debit'],
            'source' => ['required', 'in:admin,user'],
        ]);

        $from = Carbon::parse($validated['from'] ?? Carbon::today()->toDateString())->toDateString();
        $to = Carbon::parse($validated['to'] ?? Carbon::today()->toDateString())->toDateString();

        $transactions = Transaction::query()
            ->with(['user:id,name,phone', 'admin:id,name'])
            ->when(
                $validated['source'] === 'admin',
                fn ($query) => $query->whereNotNull('admin_id'),
                fn ($query) => $query->whereNull('admin_id')
            )
            ->where('type', $validated['type'])
            ->whereIn('status', ['completed', 'success', 'successful'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('backend.profit_loss_transactions', [
            'pageName' => ucfirst($validated['source']) . ' Wallet ' . ucfirst($validated['type']) . 's',
            'transactions' => $transactions,
            'type' => $validated['type'],
            'source' => $validated['source'],
            'from' => $from,
            'to' => $to,
        ]);
    }

}
