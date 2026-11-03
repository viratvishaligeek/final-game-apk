<?php

namespace App\Services;

use App\Models\Bid;
use App\Models\Game;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BidAnalyticsService
{
    public function distribution(Game $game, string $date): array
    {
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
        $harufKeys = [
            'ander' => array_map(static fn (int $number) => 'ander-' . $number, range(0, 9)),
            'bahar' => array_map(static fn (int $number) => 'bahar-' . $number, range(0, 9)),
        ];

        $jodiRows = $aggregate('jodi');
        $crossingRows = $aggregate('cross');
        $harufRows = $aggregate('haruf');

        $jodi = $toNumberRows($jodiRows, $jodiKeys);
        $crossing = $toNumberRows($crossingRows, $jodiKeys);
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

        $summaryFor = static function ($rows, int $eligibleNumbers): array {
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

        return [
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
            // Preserve the old combined response key for older admin scripts.
            'jodi_crossing' => $legacyCombined,
        ];
    }

    public function numberDetails(array $validated, string $type): array
    {
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

        // Paginate distinct users so one user's number distribution is never
        // split across pages and their other same-day numbers stay together.
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

        $users = $matchingBids->groupBy('user_id')->map(function ($rows, $userId) use ($userNumbers, $formatBid) {
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

        return [
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
        ];
    }
}
