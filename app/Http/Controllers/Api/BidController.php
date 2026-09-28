<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BidController extends Controller
{
    public function store(Request $request, Game $game)
    {
        /*
        |--------------------------------------------------------------------------
        | BASIC REQUEST VALIDATION
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'mode' => [
                'required',
                'string',
                'in:single,harup,crossing',
            ],

            'total_amount' => [
                'required',
                'numeric',
                'min:1',
            ],

            'total_bets' => [
                'required',
                'integer',
                'min:1',
            ],

            'single_bets' => [
                'nullable',
                'array',
            ],

            'harup_bets' => [
                'nullable',
                'array',
            ],

            'harup_bets.ander' => [
                'nullable',
                'array',
            ],

            'harup_bets.bahar' => [
                'nullable',
                'array',
            ],

            'crossing_jodis' => [
                'nullable',
                'array',
            ],

            'crossing_amount_per_jodi' => [
                'nullable',
                'numeric',
                'min:1',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | GAME VALIDATION
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | AUTH USER
        |--------------------------------------------------------------------------
        */

        $user = $request->user();

        if (!$user) {
            throw ValidationException::withMessages([
                'auth' => [
                    'Unauthenticated.'
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | GAME DATE
        |--------------------------------------------------------------------------
        */

        $gameDate = now()->toDateString();

        /*
        |--------------------------------------------------------------------------
        | FRONTEND MODE -> DATABASE TYPE
        |--------------------------------------------------------------------------
        */

        $type = match ($validated['mode']) {
            'single' => 'jodi',
            'harup' => 'haruf',
            'crossing' => 'cross',
        };

        /*
        |--------------------------------------------------------------------------
        | BUILD BETS
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | EMPTY BET CHECK
        |--------------------------------------------------------------------------
        */

        if (empty($bets)) {
            throw ValidationException::withMessages([
                'bets' => [
                    'Please select at least one valid bet.'
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | REMOVE DUPLICATES
        |--------------------------------------------------------------------------
        */

        $bets = collect($bets)
            ->unique(function ($bet) {
                return $bet['number'];
            })
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | SERVER CALCULATED TOTAL
        |--------------------------------------------------------------------------
        */

        $calculatedTotal = round(
            collect($bets)->sum(
                fn ($bet) => (float) $bet['amount']
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

        /*
        |--------------------------------------------------------------------------
        | CLIENT TOTAL VALIDATION
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | CLIENT COUNT VALIDATION
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | ATOMIC TRANSACTION
        |--------------------------------------------------------------------------
        */

        $result = DB::transaction(function () use (
            $user,
            $game,
            $gameDate,
            $type,
            $bets,
            $calculatedTotal
        ) {

            /*
            |--------------------------------------------------------------------------
            | LOCK USER
            |--------------------------------------------------------------------------
            */

            $lockedUser = User::query()
                ->lockForUpdate()
                ->findOrFail($user->id);

            /*
            |--------------------------------------------------------------------------
            | NUMBERS
            |--------------------------------------------------------------------------
            */

            $numbers = collect($bets)
                ->pluck('number')
                ->map(fn ($number) => (string) $number)
                ->unique()
                ->values()
                ->all();

            /*
            |--------------------------------------------------------------------------
            | WALLET BALANCE
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | ORDER NUMBER
            |--------------------------------------------------------------------------
            */

            $orderNo =
                'BET-'
                . now()->format('YmdHis')
                . '-'
                . strtoupper(Str::random(6));

            /*
            |--------------------------------------------------------------------------
            | DEDUCT WALLET
            |--------------------------------------------------------------------------
            */

            $newBalance = round(
                $balance - $calculatedTotal,
                2
            );

            $lockedUser->balance = $newBalance;
            $lockedUser->save();

            /*
            |--------------------------------------------------------------------------
            | WALLET TRANSACTION
            |--------------------------------------------------------------------------
            */

            $transaction = $lockedUser
                ->transactions()
                ->create([
                    'phone' => $lockedUser->phone,
                    'amount' => $calculatedTotal,
                    'subject' => "Bet placed - {$game->name}",
                    'balance' => $newBalance,
                    'status' => 'completed',
                    'type' => 'debit',
                ]);

            /*
            |--------------------------------------------------------------------------
            | CREATE BIDS
            |--------------------------------------------------------------------------
            */

            $createdBids = [];

            foreach ($bets as $bet) {

                $createdBids[] = Bid::create([
                    'order_no' => $orderNo,
                    'user_id' => $lockedUser->id,
                    'game_id' => $game->id,
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

        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Bet placed successfully.',
            'data' => $result,
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | SINGLE / JODI
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | HARUP
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | CROSSING
    |--------------------------------------------------------------------------
    */

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
}
