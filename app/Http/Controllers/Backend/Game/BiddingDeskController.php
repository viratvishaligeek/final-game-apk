<?php

namespace App\Http\Controllers\Backend\Game;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Game;
use App\Models\WalletRequest;
use App\Models\Winner;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BiddingDeskController extends Controller
{
    public function index()
    {
        $games = Game::query()
            ->orderBy('name')
            ->get(['id', 'name']);
        return view('backend.bidding-desk', compact('games'));
    }

    public function data(Request $request)
    {
        $validated = $request->validate([
            'game_id' => ['required', 'integer', 'exists:games,id'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $game = Game::query()->findOrFail($validated['game_id']);
        $date = $validated['date'] ?? $game->businessDate();
        $baseQuery = Bid::query()
            ->where('game_id', $game->id)
            ->where('game_date', $date);

        $aggregate = static function (string $type) use ($baseQuery) {
            return (clone $baseQuery)
                ->where('type', $type)
                ->select([
                    'number',
                    DB::raw('COUNT(*) as total_bids'),
                    DB::raw('COUNT(DISTINCT user_id) as total_users'),
                    DB::raw('SUM(amount) as total_amount'),
                ])
                ->groupBy('number')
                ->get()
                ->keyBy('number');
        };

        $toNumberRows = static function ($rows, array $keys): array {
            return array_map(static function (string $number) use ($rows) {
                $row = $rows->get($number);
                return [
                    'number' => $number,
                    'total_bids' => (int) ($row->total_bids ?? 0),
                    'total_users' => (int) ($row->total_users ?? 0),
                    'total_amount' => round((float) ($row->total_amount ?? 0), 2),
                ];
            }, $keys);
        };

        $jodiKeys = array_map(
            static fn (int $number) => str_pad((string) $number, 2, '0', STR_PAD_LEFT),
            range(0, 99)
        );
        $crossingKeys = $jodiKeys;
        $harufKeys = [
            'ander' => array_map(static fn (int $number) => 'ander-' . $number, range(0, 9)),
            'bahar' => array_map(static fn (int $number) => 'bahar-' . $number, range(0, 9)),
        ];

        $jodiRows = $aggregate('jodi');
        $crossingRows = $aggregate('cross');
        $harufRows = $aggregate('haruf');

        $jodi = $toNumberRows($jodiRows, $jodiKeys);
        $crossing = $toNumberRows($crossingRows, $crossingKeys);
        $haruf = [
            'ander' => array_map(
                static fn (array $row) => array_merge($row, ['number' => substr($row['number'], 6)]),
                $toNumberRows($harufRows, $harufKeys['ander'])
            ),
            'bahar' => array_map(
                static fn (array $row) => array_merge($row, ['number' => substr($row['number'], 6)]),
                $toNumberRows($harufRows, $harufKeys['bahar'])
            ),
        ];

        $summaryFor = static function ($rows, int $eligibleNumbers) {
            $playedNumbers = $rows->keys()->filter(
                static fn ($number) => $number !== null && $number !== ''
            )->unique()->count();

            return [
                'total_bets' => (int) $rows->sum('total_bids'),
                'total_amount' => round((float) $rows->sum('total_amount'), 2),
                'played_numbers' => $playedNumbers,
                'unplayed_numbers' => max(0, $eligibleNumbers - $playedNumbers),
                'unplayed_amount' => 0.0,
            ];
        };

        $legacyCombined = collect($jodi)
            ->map(fn (array $row) => $row + ['type' => 'jodi'])
            ->concat(collect($crossing)->map(fn (array $row) => $row + ['type' => 'cross']))
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'game_id' => $game->id,
                'game_name' => $game->name,
                'date' => $date,
                'summary' => [
                    'jodi' => $summaryFor($jodiRows, 100),
                    'haruf' => $summaryFor($harufRows, 20),
                    'crossing' => $summaryFor($crossingRows, 100),
                ],
                'jodi' => $jodi,
                'crossing' => $crossing,
                'haruf' => $haruf,
                // Keep the old combined key for any older admin scripts.
                'jodi_crossing' => $legacyCombined,
            ],
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

        if ($type === 'haruf' && !preg_match('/^(ander|bahar)-[0-9]$/', $validated['number'])) {
            throw ValidationException::withMessages(['number' => 'Invalid Haruf number.']);
        }
        if (in_array($type, ['jodi', 'cross'], true) && !preg_match('/^[0-9]{2}$/', $validated['number'])) {
            throw ValidationException::withMessages(['number' => 'Invalid Jodi/Crossing number.']);
        }

        $baseQuery = Bid::query()
            ->where('game_id', $validated['game_id'])
            ->where('game_date', $validated['date'])
            ->where('type', $type)
            ->where('number', $validated['number']);

        $totalBids = (clone $baseQuery)->count();
        $totalUsers = (clone $baseQuery)->distinct('user_id')->count('user_id');
        $totalAmount = (float) (clone $baseQuery)->sum('amount');

        // Paginate distinct users rather than bid rows so a user is never split
        // across pages and each user's complete same-day number list is coherent.
        $userPaginator = (clone $baseQuery)
            ->select('user_id')
            ->distinct()
            ->orderBy('user_id')
            ->paginate($validated['per_page'] ?? 50);

        $userIds = $userPaginator->getCollection()->pluck('user_id')->unique()->values();
        $matchingBids = $userIds->isEmpty()
            ? collect()
            : (clone $baseQuery)
                ->whereIn('user_id', $userIds)
                ->with(['user:id,name,phone'])
                ->orderByDesc('id')
                ->get();

        $bidIds = $matchingBids->pluck('id');
        $transactionIds = $bidIds->isEmpty()
            ? collect()
            : Transaction::query()
                ->whereIn('bid_id', $bidIds)
                ->get(['id', 'bid_id'])
                ->keyBy('bid_id');
        $userNumbers = $userIds->isEmpty()
            ? collect()
            : Bid::query()
                ->where('game_id', $validated['game_id'])
                ->where('game_date', $validated['date'])
                ->where('type', $type)
                ->whereIn('user_id', $userIds)
                ->select([
                    'user_id',
                    'number',
                    DB::raw('COUNT(*) as total_bids'),
                    DB::raw('SUM(amount) as total_amount'),
                    DB::raw('MAX(created_at) as latest_at'),
                ])
                ->groupBy('user_id', 'number')
                ->orderBy('number')
                ->get()
                ->groupBy('user_id');

        $formatBid = static function (Bid $bid) use ($transactionIds): array {
            return [
                'id' => $bid->id,
                'transaction_id' => $transactionIds->get($bid->id)?->id,
                'order_no' => $bid->order_no,
                'user_id' => $bid->user_id,
                'user_name' => $bid->user?->name ?? 'Unknown',
                'user_phone' => $bid->user?->phone ?? $bid->phone ?? '-',
                'type' => $bid->type,
                'number' => $bid->number,
                'amount' => round((float) $bid->amount, 2),
                'status' => $bid->status,
                'winning_amount' => round((float) $bid->winning_amount, 2),
                'created_at' => $bid->created_at?->timezone(config('app.timezone'))->format('d M Y, h:i A'),
            ];
        };

        $selectedByUser = $matchingBids->groupBy('user_id');
        $users = $selectedByUser->map(function ($rows, $userId) use ($userNumbers, $formatBid) {
            $user = $rows->first()->user;
            $numbers = $userNumbers->get($userId, collect())->map(fn ($row) => [
                'number' => $row->number,
                'total_bids' => (int) $row->total_bids,
                'total_amount' => round((float) $row->total_amount, 2),
                'latest_at' => $row->latest_at,
            ])->values();

            return [
                'user_id' => (int) $userId,
                'user_name' => $user?->name ?? 'Unknown',
                'user_phone' => $user?->phone ?? $rows->first()->phone ?? '-',
                'matching_bids' => $rows->map($formatBid)->values(),
                'numbers' => $numbers,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'number' => $validated['number'],
                'type' => $type,
                'summary' => [
                    'total_bids' => $totalBids,
                    'total_users' => $totalUsers,
                    'total_amount' => round($totalAmount, 2),
                ],
                'users' => $users,
                'bids' => $matchingBids->map($formatBid)->values(),
                'pagination' => [
                    'current_page' => $userPaginator->currentPage(),
                    'last_page' => $userPaginator->lastPage(),
                    'per_page' => $userPaginator->perPage(),
                    'total' => $userPaginator->total(),
                ],
            ],
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
