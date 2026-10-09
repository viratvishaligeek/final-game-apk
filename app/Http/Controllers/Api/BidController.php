<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Game;
use App\Models\User;
use App\Services\AppSettingsService;
use App\Services\WalletService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BidController extends Controller
{
    public function __construct(
        protected AppSettingsService $settings,
        protected WalletService $walletService
    ) {}

    public function store(Request $request, Game $game)
    {
        $validated = $request->validate([
            'mode' => ['required', 'string', 'in:single,harup,crossing'],
            'total_amount' => ['required', 'numeric', 'decimal:0,2', 'min:1'],
            'total_bets' => ['required', 'integer', 'min:1'],
            'single_bets' => ['nullable', 'array'],
            'single_bets.*' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
            'harup_bets' => ['nullable', 'array'],
            'harup_bets.ander' => ['nullable', 'array'],
            'harup_bets.ander.*' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
            'harup_bets.bahar' => ['nullable', 'array'],
            'harup_bets.bahar.*' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
            'crossing_jodis' => ['nullable', 'array'],
            'crossing_jodis.*' => ['required', 'digits:2'],
            'crossing_amount_per_jodi' => ['nullable', 'numeric', 'decimal:0,2', 'min:1'],
        ]);
        if ($game->status !== 'active') {
            throw ValidationException::withMessages([
                'game' => [
                    'This game is currently inactive.'
                ],
            ]);
        }
        if (!$game->is_playable) {
            throw ValidationException::withMessages([
                'game' => [
                    'Betting time for this game is closed.'
                ],
            ]);
        }
        $user = $request->user();
        if (!$user) {
            throw ValidationException::withMessages([
                'auth' => [
                    'Unauthenticated.'
                ],
            ]);
        }
        $type = match ($validated['mode']) {
            'single' => 'jodi',
            'harup' => 'haruf',
            'crossing' => 'cross',
        };
        $bets = match ($validated['mode']) {
            'single' => $this->buildSingleBets(
                $validated['single_bets'] ?? []
            ),
            'harup' => $this->buildHarupBets(
                $validated['harup_bets'] ?? []
            ),
            'crossing' => $this->buildCrossingBets(
                $validated['crossing_jodis'] ?? [],
                $validated['crossing_amount_per_jodi'] ?? 0
            ),
        };
        if (empty($bets)) {
            throw ValidationException::withMessages([
                'bets' => ['Please select at least one valid bet.'],
            ]);
        }
        $limits = match ($validated['mode']) {
            'single', 'crossing' => [
                'min_bid_amount_jodi',
                'max_bid_amount_jodi',
            ],
            'harup' => [
                'min_bid_amount_haruf',
                'max_bid_amount_haruf',
            ],
        };

        [$minimumKey, $maximumKey] = $limits;

        foreach ($bets as $bet) {
            $this->settings->validateRange(
                (float) $bet['amount'],
                $minimumKey,
                $maximumKey,
                'bets'
            );
        }


        $bets = collect($bets)
            ->unique(function ($bet) {
                return $bet['number'];
            })
            ->values()
            ->all();
        $calculatedTotal = round(
            collect($bets)->sum(
                fn($bet) => (float) $bet['amount']
            ),
            2
        );
        if ($calculatedTotal <= 0) {
            throw ValidationException::withMessages([
                'amount' => [
                    'Invalid bet amount.'
                ],
            ]);
        }

        $clientTotal = round(
            (float) $validated['total_amount'],
            2
        );
        if (
            (int) round($calculatedTotal * 100)
            !==
            (int) round($clientTotal * 100)
        ) {
            throw ValidationException::withMessages([
                'total_amount' => [
                    'Bet amount validation failed.'
                ],
            ]);
        }

        $serverBetCount = count($bets);
        if (
            (int) $validated['total_bets']
            !==
            $serverBetCount
        ) {
            throw ValidationException::withMessages([
                'total_bets' => [
                    'Bet count validation failed.'
                ],
            ]);
        }

        $result = DB::transaction(function () use (
            $user,
            $game,
            $type,
            $bets,
            $calculatedTotal
        ) {
            // Serialize betting with result publication by locking the game first.
            $lockedGame = Game::query()
                ->lockForUpdate()
                ->findOrFail($game->id);

            if ($lockedGame->status !== 'active' || !$lockedGame->isPlayableAt()) {
                throw ValidationException::withMessages([
                    'game' => ['Betting time for this game is closed.'],
                ]);
            }

            $gameDate = $lockedGame->businessDate();

            $lockedUser = User::query()
                ->lockForUpdate()
                ->findOrFail($user->id);

            $balance = round(
                (float) $lockedUser->balance,
                2
            );
            if ($balance < $calculatedTotal) {
                throw ValidationException::withMessages([
                    'amount' => [
                        'Insufficient wallet balance.'
                    ],
                ]);
            }
            $orderNo =
                'BET-'
                . now()->format('YmdHis')
                . '-'
                . strtoupper(Str::random(6));

            $transaction = $this->walletService->debit(
                $lockedUser,
                $calculatedTotal,
                "Bet placed - {$lockedGame->name}"
            );

            $newBalance = round((float) $transaction->balance, 2);
            $createdBids = [];
            foreach ($bets as $bet) {
                $createdBids[] = Bid::create([
                    'order_no' => $orderNo,
                    'user_id' => $lockedUser->id,
                    'game_id' => $lockedGame->id,
                    'phone' => $lockedUser->phone,
                    'game_date' => $gameDate,
                    'type' => $type,
                    'number' => $bet['number'],
                    'amount' => $bet['amount'],
                    'status' => 'pending',
                    'winning_amount' => 0,
                ]);
            }
            return [
                'order_no' => $orderNo,
                'transaction_id' => $transaction->id,
                'balance' => $newBalance,
                'total_amount' => $calculatedTotal,
                'total_bets' => count($createdBids),
            ];
        });
        return response()->json([
            'success' => true,
            'message' => 'Bet placed successfully.',
            'data' => $result,
        ], 201);
    }

    private function buildSingleBets(array $singleBets): array
    {
        $bets = [];
        foreach ($singleBets as $number => $amount) {
            $number = (string) $number;
            if (!preg_match('/^\d{2}$/', $number)) {
                continue;
            }
            $amount = (float) $amount;
            if ($amount < 1) {
                continue;
            }
            $bets[] = [
                'number' => $number,
                'amount' => $amount,
            ];
        }
        return $bets;
    }

    private function buildHarupBets(array $harupBets): array
    {
        $bets = [];
        foreach (['ander', 'bahar'] as $side) {
            $digits = $harupBets[$side] ?? [];
            if (!is_array($digits)) {
                continue;
            }
            foreach ($digits as $digit => $amount) {
                if (
                    !is_numeric($digit)
                    ||
                    !is_numeric($amount)
                ) {
                    continue;
                }
                $digit = (int) $digit;
                $amount = (float) $amount;
                if ($digit < 0 || $digit > 9) {
                    continue;
                }
                if ($amount < 1) {
                    continue;
                }
                $bets[] = [
                    'number' => $side . '-' . $digit,
                    'amount' => $amount,
                ];
            }
        }
        return $bets;
    }

    private function buildCrossingBets(
        array $jodis,
        $amountPerJodi
    ): array {
        $amountPerJodi = (float) $amountPerJodi;
        if ($amountPerJodi < 1) {
            return [];
        }
        $bets = [];
        foreach ($jodis as $jodi) {
            $jodi = (string) $jodi;
            if (!preg_match('/^\d{2}$/', $jodi)) {
                continue;
            }
            $bets[] = [
                'number' => $jodi,
                'amount' => $amountPerJodi,
            ];
        }
        return collect($bets)
            ->unique('number')
            ->values()
            ->all();
    }

    public function history(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'date' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
            ],
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:50',
            ],
        ]);

        $hasExplicitDate = isset($validated['date']);
        $date = $validated['date'] ?? now()->toDateString();
        $perPage = $validated['per_page'] ?? 50;
        $page = $validated['page'] ?? 1;

        $businessDates = [];
        if (!$hasExplicitDate) {
            $businessDates = Game::withTrashed()
                ->get(['id', 'name', 'slug', 'result_time'])
                ->mapWithKeys(fn (Game $game) => [$game->id => $game->businessDate()])
                ->all();
        }

        /*
    |--------------------------------------------------------------------------
    | First get slip/order numbers
    |--------------------------------------------------------------------------
    */

        $orderQuery = $this->applyHistoryDateFilter(
            Bid::query()->where('user_id', $user->id),
            $hasExplicitDate ? $date : null,
            $businessDates
        )
            ->select('order_no')
            ->groupBy('order_no')
            ->orderByDesc(DB::raw('MAX(id)'));

        $orderPaginator = $orderQuery->paginate(
            $perPage,
            ['order_no'],
            'page',
            $page
        );

        $orderNos = collect($orderPaginator->items())
            ->pluck('order_no')
            ->values();

        /*
    |--------------------------------------------------------------------------
    | Now get ALL bids belonging to those slips
    |--------------------------------------------------------------------------
    */

        $bids = $this->applyHistoryDateFilter(
            Bid::query()
                ->with(['game:id,name,status'])
                ->where('user_id', $user->id),
            $hasExplicitDate ? $date : null,
            $businessDates
        )
            ->whereIn('order_no', $orderNos)
            ->orderByDesc('id')
            ->get();

        /*
    |--------------------------------------------------------------------------
    | Format complete slips
    |--------------------------------------------------------------------------
    */

        $slips = $bids
            ->groupBy('order_no')
            ->map(function ($slipBids) {
                return $this->formatSlip($slipBids);
            })
            ->values();

        /*
    |--------------------------------------------------------------------------
    | Stats
    |--------------------------------------------------------------------------
    */

        $totalSpent = $slips->sum('total_amount');
        $totalWon = $slips->sum('win_amount');

        return response()->json([
            'success' => true,

            'data' => [
                'date' => $date,
                'date_mode' => $hasExplicitDate ? 'calendar_date' : 'current_business_dates',

                'stats' => [
                    'total_slips' => $orderPaginator->total(),
                    'total_spent' => round($totalSpent, 2),
                    'total_won' => round($totalWon, 2),
                ],

                'slips' => $slips,

                'pagination' => [
                    'current_page' => $orderPaginator->currentPage(),
                    'last_page' => $orderPaginator->lastPage(),
                    'per_page' => $orderPaginator->perPage(),
                    'total' => $orderPaginator->total(),
                    'from' => $orderPaginator->firstItem(),
                    'to' => $orderPaginator->lastItem(),
                ],
            ],
        ]);
    }
    /**
     * An explicit date filters by that exact stored game_date. Without one,
     * include each game's current business date so Disawar's early-morning
     * history is not lost behind the calendar-date boundary.
     */
    private function applyHistoryDateFilter($query, ?string $date, array $businessDates)
    {
        if ($date !== null) {
            return $query->where('game_date', $date);
        }

        return $query->where(function ($outer) use ($businessDates) {
            if ($businessDates === []) {
                $outer->whereRaw('1 = 0');
                return;
            }

            foreach ($businessDates as $gameId => $gameDate) {
                $outer->orWhere(function ($perGame) use ($gameId, $gameDate) {
                    $perGame->where('game_id', $gameId)
                        ->where('game_date', $gameDate);
                });
            }
        });
    }

    private function formatSlip($bids): array
    {
        $firstBid = $bids->first();
        $game = $firstBid->game;

        $mode = match ($firstBid->type) {
            'jodi' => 'single',
            'haruf' => 'harup',
            'cross' => 'crossing',
            default => $firstBid->type,
        };

        $winningAmount = round(
            $bids->sum(fn($bid) => (float) $bid->winning_amount),
            2
        );

        $hasWon = $winningAmount > 0;

        $isPending = $bids->contains(function ($bid) {
            return $bid->status === 'pending';
        });

        /*
    |--------------------------------------------------------------------------
    | Get complete result
    |--------------------------------------------------------------------------
    */

        $result = $this->getGameResult(
            $game,
            $firstBid->game_date
        );

        $status = $this->getSlipStatus(
            $game,
            $bids,
            $hasWon,
            $isPending,
            $result
        );

        return [
            'id' => $firstBid->id,

            'slip_code' => $firstBid->order_no,

            'game_id' => $firstBid->game_id,

            'game_name' => $game?->name ?? 'Unknown Game',

            'mode' => $mode,

            'date' => Carbon::parse($firstBid->game_date)
                ->format('Y-m-d'),

            'created_at' => $firstBid->created_at,

            'total_amount' => round(
                $bids->sum(fn($bid) => (float) $bid->amount),
                2
            ),

            'total_bets_count' => $bids->count(),

            'status' => $status,

            /*
        |--------------------------------------------------------------------------
        | Results
        |--------------------------------------------------------------------------
        */

            'winning_number' => $result['jodi'],

            'harup_result' => [
                'ander' => $result['ander'],
                'bahar' => $result['bahar'],
            ],

            /*
        |--------------------------------------------------------------------------
        | Winning amount
        |--------------------------------------------------------------------------
        */

            'win_amount' => $winningAmount,

            /*
        |--------------------------------------------------------------------------
        | Bet details
        |--------------------------------------------------------------------------
        */

            'single_bets' => $this->getSingleBets(
                $bids,
                $mode
            ),

            'harup_bets' => $this->getHarupBets(
                $bids,
                $mode
            ),

            'crossing_digits' => $this->getCrossingDigits(
                $bids,
                $mode
            ),

            'crossing_amount_per_jodi' => $this->getCrossingAmount(
                $bids,
                $mode
            ),

            'crossing_jodis' => $this->getCrossingJodis(
                $bids,
                $mode
            ),
        ];
    }

    private function getSlipStatus(
        $game,
        $bids,
        bool $hasWon,
        bool $isPending,
        array $result
    ): string {

        /*
    |--------------------------------------------------------------------------
    | If winning amount exists, definitely won
    |--------------------------------------------------------------------------
    */

        if ($hasWon) {
            return 'won';
        }

        /*
    |--------------------------------------------------------------------------
    | Explicit failed/lost/rejected
    |--------------------------------------------------------------------------
    */

        if ($bids->contains(function ($bid) {
            return in_array(
                $bid->status,
                ['lost', 'failed', 'rejected'],
                true
            );
        })) {
            return 'lost';
        }

        /*
    |--------------------------------------------------------------------------
    | Result exists
    |--------------------------------------------------------------------------
    */

        $hasResult =
            !empty($result['jodi']) ||
            !empty($result['ander']) ||
            !empty($result['bahar']);

        if ($hasResult) {
            return 'lost';
        }

        /*
    |--------------------------------------------------------------------------
    | Game still running
    |--------------------------------------------------------------------------
    */

        if ($isPending && $game?->is_playable) {
            return 'running';
        }

        /*
    |--------------------------------------------------------------------------
    | No pending + no win
    |--------------------------------------------------------------------------
    */

        if (!$isPending) {
            return 'lost';
        }

        return 'running';
    }

    private function getGameResult($game, $date): array
    {
        $result = [
            'ander' => null,
            'bahar' => null,
            'jodi' => null,
        ];

        if (!$game) {
            return $result;
        }

        $results = $game->results()
            ->whereDate('game_date', $date)
            ->whereIn('type', ['ah', 'bh', 'jodi'])
            ->orderByDesc('id')
            ->get();

        foreach ($results as $item) {

            $number = $item->number;

            if ($number === null || $number === 'Wait') {
                continue;
            }

            switch ($item->type) {

                case 'ah':
                    if ($result['ander'] === null) {
                        $result['ander'] = (string) $number;
                    }
                    break;

                case 'bh':
                    if ($result['bahar'] === null) {
                        $result['bahar'] = (string) $number;
                    }
                    break;

                case 'jodi':
                    if ($result['jodi'] === null) {
                        $result['jodi'] = (string) $number;
                    }
                    break;
            }
        }

        return $result;
    }

    // private function getWinningNumber($game, $date): ?string
    // {
    //     if (!$game) {
    //         return null;
    //     }

    //     $result = $game->results()
    //         ->whereDate('game_date', $date)
    //         ->where('type', 'jodi')
    //         ->latest('id')
    //         ->first();

    //     if (!$result || !$result->number) {
    //         return null;
    //     }

    //     if ($result->number === 'Wait') {
    //         return null;
    //     }

    //     return (string) $result->number;
    // }

    private function getSingleBets($bids, string $mode): array
    {
        if ($mode !== 'single') {
            return [];
        }

        return $bids
            ->mapWithKeys(function ($bid) {
                return [
                    (string) $bid->number => (float) $bid->amount,
                ];
            })
            ->toArray();
    }

    private function getHarupBets($bids, string $mode): array
    {
        if ($mode !== 'harup') {
            return [
                'ander' => [],
                'bahar' => [],
            ];
        }

        $result = [
            'ander' => [],
            'bahar' => [],
        ];

        foreach ($bids as $bid) {
            $parts = explode('-', (string) $bid->number, 2);

            if (count($parts) !== 2) {
                continue;
            }

            [$side, $digit] = $parts;

            if (!isset($result[$side])) {
                continue;
            }

            $result[$side][(string) $digit] = (float) $bid->amount;
        }

        return $result;
    }

    private function getCrossingDigits($bids, string $mode): array
    {
        if ($mode !== 'crossing') {
            return [];
        }

        return $bids
            ->flatMap(function ($bid) {
                $number = str_pad(
                    (string) $bid->number,
                    2,
                    '0',
                    STR_PAD_LEFT
                );

                return [
                    (int) $number[0],
                    (int) $number[1],
                ];
            })
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    private function getCrossingAmount($bids, string $mode): float
    {
        if ($mode !== 'crossing') {
            return 0;
        }

        return (float) ($bids->first()?->amount ?? 0);
    }

    private function getCrossingJodis($bids, string $mode): array
    {
        if ($mode !== 'crossing') {
            return [];
        }

        return $bids
            ->pluck('number')
            ->map(fn($number) => (string) $number)
            ->values()
            ->toArray();
    }
}
