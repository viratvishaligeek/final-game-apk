<?php

namespace App\Http\Controllers\Backend\Game;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Game;
use Illuminate\Http\Request;
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
            'game_id' => [
                'required',
                'integer',
                'exists:games,id',
            ],

            'date' => [
                'nullable',
                'date',
            ],
        ]);

        $gameId = $validated['game_id'];

        $date = $validated['date']
            ?? now()->toDateString();

        $jodiCrossing = Bid::query()
            ->where('game_id', $gameId)
            ->where('game_date', $date)
            ->whereIn('type', ['jodi', 'cross'])
            ->select([
                'number',
                DB::raw('COUNT(*) as total_bids'),
                DB::raw('COUNT(DISTINCT user_id) as total_users'),
                DB::raw('SUM(amount) as total_amount'),
            ])
            ->groupBy('number')
            ->get()
            ->keyBy('number');

        $jodiCrossingNumbers = [];

        for ($i = 0; $i <= 99; $i++) {

            $number = str_pad(
                (string) $i,
                2,
                '0',
                STR_PAD_LEFT
            );

            $row = $jodiCrossing->get($number);

            $jodiCrossingNumbers[] = [
                'number' => $number,

                'total_bids' => $row
                    ? (int) $row->total_bids
                    : 0,

                'total_users' => $row
                    ? (int) $row->total_users
                    : 0,

                'total_amount' => $row
                    ? round((float) $row->total_amount, 2)
                    : 0,
            ];
        }

        $haruf = Bid::query()
            ->where('game_id', $gameId)
            ->where('game_date', $date)
            ->where('type', 'haruf')
            ->select([
                'number',
                DB::raw('COUNT(*) as total_bids'),
                DB::raw('COUNT(DISTINCT user_id) as total_users'),
                DB::raw('SUM(amount) as total_amount'),
            ])
            ->groupBy('number')
            ->get()
            ->keyBy('number');

        /*
        |--------------------------------------------------------------------------
        | ANDER
        |--------------------------------------------------------------------------
        */

        $ander = [];

        for ($i = 0; $i <= 9; $i++) {

            $number = 'ander-' . $i;

            $row = $haruf->get($number);

            $ander[] = [
                'number' => (string) $i,

                'total_bids' => $row
                    ? (int) $row->total_bids
                    : 0,

                'total_users' => $row
                    ? (int) $row->total_users
                    : 0,

                'total_amount' => $row
                    ? round((float) $row->total_amount, 2)
                    : 0,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | BAHAR
        |--------------------------------------------------------------------------
        */

        $bahar = [];

        for ($i = 0; $i <= 9; $i++) {

            $number = 'bahar-' . $i;

            $row = $haruf->get($number);

            $bahar[] = [
                'number' => (string) $i,

                'total_bids' => $row
                    ? (int) $row->total_bids
                    : 0,

                'total_users' => $row
                    ? (int) $row->total_users
                    : 0,

                'total_amount' => $row
                    ? round((float) $row->total_amount, 2)
                    : 0,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'data' => [
                'game_id' => $gameId,
                'date' => $date,

                'jodi_crossing' => $jodiCrossingNumbers,

                'haruf' => [
                    'ander' => $ander,
                    'bahar' => $bahar,
                ],
            ],
        ]);
    }

    /**
     * AJAX: Number Details
     */
    public function details(Request $request)
    {
        $validated = $request->validate([
            'game_id' => ['required', 'integer', 'exists:games,id'],
            'date' => ['required', 'date'],
            'number' => ['required', 'string', 'max:30'],
        ]);

        $bids = Bid::query()
            ->with([
                'user:id,name,phone',
            ])
            ->where('game_id', $validated['game_id'])
            ->where('game_date', $validated['date'])
            ->where('number', $validated['number'])
            ->when(
                str_starts_with(
                    $validated['number'],
                    'ander-'
                ) ||
                    str_starts_with(
                        $validated['number'],
                        'bahar-'
                    ),
                function ($query) {
                    $query->where('type', 'haruf');
                },
                function ($query) {
                    $query->whereIn(
                        'type',
                        ['jodi', 'cross']
                    );
                }
            )
            ->orderByDesc('id')
            ->get([
                'id',
                'order_no',
                'user_id',
                'type',
                'number',
                'amount',
                'status',
                'winning_amount',
                'created_at',
            ]);

        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */

        $summary = [
            'total_bids' => $bids->count(),

            'total_users' => $bids
                ->pluck('user_id')
                ->unique()
                ->count(),

            'total_amount' => round(
                (float) $bids->sum('amount'),
                2
            ),
        ];

        return response()->json([
            'success' => true,

            'data' => [
                'number' => $validated['number'],

                'summary' => $summary,

                'bids' => $bids->map(function ($bid) {

                    return [
                        'id' => $bid->id,

                        'order_no' => $bid->order_no,

                        'user_id' => $bid->user_id,

                        'user_name' =>
                        $bid->user?->name ?? 'Unknown',

                        'user_phone' =>
                        $bid->user?->phone
                            ?? $bid->phone
                            ?? '-',

                        'type' => $bid->type,

                        'number' => $bid->number,

                        'amount' =>
                        round((float) $bid->amount, 2),

                        'status' => $bid->status,

                        'winning_amount' =>
                        round(
                            (float) $bid->winning_amount,
                            2
                        ),

                        'created_at' =>
                        optional(
                            $bid->created_at
                        )->format(
                            'd M Y, h:i A'
                        ),
                    ];
                }),
            ],
        ]);
    }
}
