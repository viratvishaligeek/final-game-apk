<?php

namespace App\Http\Controllers\Backend\Game;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Game;
use App\Models\WalletRequest;
use App\Models\Winner;
use App\Services\Admin\BiddingDeskProfitLossReportService;
use App\Models\Transaction;
use App\Services\BidAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
            'game_id' => [
                'required', 'integer',
                Rule::exists('games', 'id')->where(
                    fn ($query) => $query->where('site_key', config('sites.current_key'))
                ),
            ],
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
            'game_id' => [
                'required', 'integer',
                Rule::exists('games', 'id')->where(
                    fn ($query) => $query->where('site_key', config('sites.current_key'))
                ),
            ],
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
    public function profitLoss(Request $request, BiddingDeskProfitLossReportService $reportService)
    {
        $today = Carbon::today()->toDateString();
        $from = $request->get('from', $today);
        $to = $request->get('to', $today);

        return view('backend.profit_loss_history', $reportService->build($from, $to));
    }

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
