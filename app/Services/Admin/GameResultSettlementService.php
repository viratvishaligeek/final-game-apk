<?php

namespace App\Services\Admin;

use App\Models\Bid;
use App\Models\Game;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Winner;
use Illuminate\Validation\ValidationException;

class GameResultSettlementService
{
    /**
     * Settle winning bids and wallet credits inside the caller's transaction.
     */
    public function settleGameBids(
            Game $game,
            int $gameId,
            string $gameDate,
            string $jodi,
            string $ah,
            string $bh
        ): array {
            $reward = (float) ($game->reward ?? 1);
    
            $winningHarufNumbers = ['ander-' . $ah, 'bahar-' . $bh];
    
            $bids = Bid::query()
                ->where('game_id', $gameId)
                ->where('game_date', $gameDate)
                ->where('status', 'pending')
                ->where(function ($query) use ($jodi, $winningHarufNumbers) {
                    // Crossing selections are Jodi combinations too; a matching
                    // crossing number receives the same reward as a single Jodi.
                    $query->where(function ($jodiQuery) use ($jodi) {
                        $jodiQuery->whereIn('type', ['jodi', 'cross'])
                            ->where('number', $jodi);
                    })->orWhere(function ($harufQuery) use ($winningHarufNumbers) {
                        $harufQuery->where('type', 'haruf')
                            ->whereIn('number', $winningHarufNumbers);
                    });
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
                $winningAmount = $bid->type === 'haruf'
                    ? $bidAmount * ($reward / 10)
                    : $bidAmount * $reward;
    
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
    
            // Mark every non-winning bid as settled so reports do not leave
            // losing bets in the pending state after a result is published.
            Bid::query()
                ->where('game_id', $gameId)
                ->where('game_date', $gameDate)
                ->where('status', 'pending')
                ->update(['status' => 'loss']);
    
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

    /**
     * Reverse a prior result settlement while preserving wallet audit entries.
     */
    public function reverseSettlement(Game $game, string $gameDate): void
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
    
            // A corrected or reverted result must reopen previous losers too,
            // because a different winning number may make them winners.
            Bid::query()
                ->where('game_id', $game->id)
                ->where('game_date', $gameDate)
                ->where('status', 'loss')
                ->update(['status' => 'pending']);
    
            Winner::query()
                ->where('game_id', $game->id)
                ->where('game_date', $gameDate)
                ->delete();
        }
}
