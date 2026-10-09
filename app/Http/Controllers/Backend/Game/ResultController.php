<?php

namespace App\Http\Controllers\Backend\Game;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Game;
use App\Models\Notification;
use App\Models\Result;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Winner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
        ]);

        $selectedDate = $validated['date'] ?? now()->toDateString();

        $allGames = Game::query()
            ->where('status', 'active')
            ->orderBy('serial')
            ->get();

        $existingResults = Result::query()
            ->where('type', 'jodi')
            ->where('game_date', $selectedDate)
            ->get()
            ->keyBy('game_id');

        return view('backend.result.index', compact(
            'allGames',
            'existingResults',
            'selectedDate',
        ));
    }

    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'game_id' => ['required', 'integer', 'exists:games,id'],
            'game_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'result' => ['required', 'digits:2'],
        ]);

        $gameId = (int) $validated['game_id'];
        $gameDate = $validated['game_date'];
        $jodi = str_pad((string) $validated['result'], 2, '0', STR_PAD_LEFT);
        $ah = $jodi[0];
        $bh = $jodi[1];

        try {
            $settlement = DB::transaction(function () use (
                $gameId,
                $gameDate,
                $jodi,
                $ah,
                $bh
            ) {
                $game = Game::query()
                    ->lockForUpdate()
                    ->findOrFail($gameId);

                $previousJodi = Result::query()
                    ->where('game_id', $gameId)
                    ->where('game_date', $gameDate)
                    ->where('type', 'jodi')
                    ->lockForUpdate()
                    ->value('number');

                if ($previousJodi !== null && (string) $previousJodi === $jodi) {
                    return [
                        'game' => $game,
                        'stats' => ['winners' => 0, 'total_amount' => 0.0],
                        'unchanged' => true,
                    ];
                }

                // A corrected result must first reverse its prior settlement.
                // All changes stay inside the same transaction.
                if ($previousJodi !== null && (string) $previousJodi !== $jodi) {
                    $this->reverseSettlement($game, $gameDate);
                }

                $game->update([
                    'last_result' => $jodi,
                ]);

                Result::updateOrCreate(
                    [
                        'game_id' => $gameId,
                        'game_date' => $gameDate,
                        'type' => 'ah',
                    ],
                    ['number' => $ah]
                );

                Result::updateOrCreate(
                    [
                        'game_id' => $gameId,
                        'game_date' => $gameDate,
                        'type' => 'bh',
                    ],
                    ['number' => $bh]
                );

                Result::updateOrCreate(
                    [
                        'game_id' => $gameId,
                        'game_date' => $gameDate,
                        'type' => 'jodi',
                    ],
                    ['number' => $jodi]
                );

                $stats = $this->settleGameBids(
                    game: $game,
                    gameId: $gameId,
                    gameDate: $gameDate,
                    jodi: $jodi,
                    ah: $ah,
                    bh: $bh
                );

                return ['game' => $game, 'stats' => $stats];
            });

            $game = $settlement['game'];
            $stats = $settlement['stats'];

            if ($settlement['unchanged'] ?? false) {
                return redirect()->back()->with(
                    'info',
                    "Result ({$jodi}) is already published for {$gameDate}. No wallet changes were made."
                );
            }

            $message = sprintf('Result for %s is %s', $game->name, $jodi);

            Notification::create([
                'user_id' => null,
                'subject' => 'Game Result Out',
                'message' => $message,
            ]);

            $this->sendResultNotification($game, $gameDate, $jodi);

            return redirect()->back()->with(
                'success',
                "Result ({$jodi}) published for {$gameDate}. {$stats['winners']} winner(s) credited."
            );
        } catch (\Throwable $e) {
            Log::error('Result settlement failed', [
                'game_id' => $gameId,
                'game_date' => $gameDate,
                'result' => $jodi,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', $this->resultErrorMessage($e));
        }
    }

    private function settleGameBids(
        Game $game,
        int $gameId,
        string $gameDate,
        string $jodi,
        string $ah,
        string $bh
    ): array {
        $reward = (float) ($game->reward ?? 1);

        $winningNumbers = [
            'jodi' => [$jodi],
            'haruf' => ['ander-' . $ah, 'bahar-' . $bh],
        ];

        $bids = Bid::query()
            ->where('game_id', $gameId)
            ->where('game_date', $gameDate)
            ->where('status', 'pending')
            ->where(function ($query) use ($winningNumbers) {
                foreach ($winningNumbers as $type => $numbers) {
                    $query->orWhere(function ($query) use ($type, $numbers) {
                        $query->where('type', $type)
                            ->whereIn('number', $numbers);
                    });
                }
            })
            ->orderBy('user_id')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $winnerCount = 0;
        $totalWinningAmount = 0.0;

        foreach ($bids as $bid) {
            $user = User::query()
                ->lockForUpdate()
                ->find($bid->user_id);

            if (!$user) {
                throw new \RuntimeException("Missing user for winning bid {$bid->id}.");
            }

            $bidAmount = (float) $bid->amount;
            $winningAmount = $bid->type === 'jodi'
                ? $bidAmount * $reward
                : $bidAmount * ($reward / 10);

            $winningAmount = round($winningAmount, 2);
            $newBalance = round((float) $user->balance + $winningAmount, 2);

            $user->update(['balance' => $newBalance]);

            $bid->update([
                'status' => 'win',
                'winning_amount' => $winningAmount,
            ]);

            Winner::create([
                'bid_id' => $bid->id,
                'user_id' => $user->id,
                'game_id' => $gameId,
                'game_date' => $gameDate,
                'type' => $bid->type,
                'number' => $bid->number,
                'amount' => $bidAmount,
                'winning_amount' => $winningAmount,
            ]);

            Transaction::create([
                'user_id' => $user->id,
                'amount' => $winningAmount,
                'balance' => $newBalance,
                'subject' => sprintf('You Win %s (%s)', $game->name, strtoupper($bid->type)),
                'type' => 'credit',
                'status' => 'completed',
                'game_id' => $gameId,
                'bid_id' => $bid->id,
            ]);

            $winnerCount++;
            $totalWinningAmount += $winningAmount;
        }

        return [
            'winners' => $winnerCount,
            'total_amount' => round($totalWinningAmount, 2),
        ];
    }

    /**
     * Reverse a game's prior winner credits before correcting or removing a result.
     *
     * A reversal is written as a new debit transaction; the original credit is
     * preserved for audit. If a winner has already spent the credited funds,
     * abort instead of silently creating a negative wallet balance.
     */
    private function reverseSettlement(Game $game, string $gameDate): void
    {
        $winners = Winner::query()
            ->where('game_id', $game->id)
            ->where('game_date', $gameDate)
            ->orderBy('user_id')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($winners as $winner) {
            $user = User::query()
                ->lockForUpdate()
                ->findOrFail($winner->user_id);

            $winningAmount = round((float) $winner->winning_amount, 2);
            $currentBalance = round((float) $user->balance, 2);

            if ($currentBalance < $winningAmount) {
                throw ValidationException::withMessages([
                    'result' => [
                        "Cannot reverse this result safely: user {$user->id} has a balance below the reversal amount. Reconcile the wallet before retrying.",
                    ],
                ]);
            }

            $newBalance = round($currentBalance - $winningAmount, 2);

            $user->update(['balance' => $newBalance]);

            Transaction::create([
                'user_id' => $user->id,
                'amount' => $winningAmount,
                'balance' => $newBalance,
                'subject' => sprintf(
                    'Result reversal - %s (%s, %s)',
                    $game->name,
                    strtoupper($winner->type),
                    $gameDate
                ),
                'type' => 'debit',
                'status' => 'completed',
                'game_id' => $game->id,
                'bid_id' => $winner->bid_id,
            ]);

            Bid::query()
                ->whereKey($winner->bid_id)
                ->update([
                    'status' => 'pending',
                    'winning_amount' => 0,
                ]);
        }

        Winner::query()
            ->where('game_id', $game->id)
            ->where('game_date', $gameDate)
            ->delete();
    }

    private function resultErrorMessage(\Throwable $exception): string
    {
        if ($exception instanceof ValidationException) {
            $errors = $exception->errors();
            $firstError = collect($errors)->flatten()->first();

            if (is_string($firstError) && $firstError !== '') {
                return $firstError;
            }
        }

        return 'Unable to publish or reverse result. Please review the logs and wallet state.';
    }

    /** Send FCM result notification. */
    private function sendResultNotification(Game $game, string $gameDate, string $result): void
    {
        try {
            Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'key=' . config('services.fcm.server_key'),
                    'Content-Type' => 'application/json',
                ])
                ->post(config('services.fcm.url'), [
                    'to' => '/topics/weather',
                    'notification' => [
                        'title' => $game->name . ' Result Published',
                        'body' => "Result for {$game->name} is {$result}",
                    ],
                ]);
        } catch (\Throwable $e) {
            Log::error('FCM notification failed', [
                'game_id' => $game->id,
                'game_date' => $gameDate,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /** Revert a published result and reverse its settlements atomically. */
    public function revert(Request $request)
    {
        $validated = $request->validate([
            'game_id' => ['required', 'integer', 'exists:games,id'],
            'game_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
        ]);

        $gameId = (int) $validated['game_id'];
        $gameDate = $validated['game_date'];

        try {
            DB::transaction(function () use ($gameId, $gameDate) {
                $game = Game::query()
                    ->lockForUpdate()
                    ->findOrFail($gameId);

                $this->reverseSettlement($game, $gameDate);

                Result::query()
                    ->where('game_id', $gameId)
                    ->where('game_date', $gameDate)
                    ->delete();

                $previousResult = Result::query()
                    ->where('game_id', $gameId)
                    ->where('type', 'jodi')
                    ->where('game_date', '<', $gameDate)
                    ->orderByDesc('game_date')
                    ->value('number');

                $game->update([
                    'last_result' => $previousResult ?? 'Wait',
                ]);
            });

            return redirect()->back()->with(
                'success',
                'Result reverted and winner credits reversed successfully.'
            );
        } catch (\Throwable $e) {
            Log::error('Result revert failed', [
                'game_id' => $gameId,
                'game_date' => $gameDate,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', $this->resultErrorMessage($e));
        }
    }
}
