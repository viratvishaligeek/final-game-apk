<?php

namespace App\Http\Controllers\Backend;

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

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->input('date', now()->toDateString());
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
            'game_id' => ['required', 'integer', 'exists:games,id',],
            'game_date' => ['required', 'date_format:Y-m-d',],
            'result' => ['required', 'digits:2',],
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
                $game = Game::query()->lockForUpdate()->findOrFail($gameId);
                $game->update([
                    'last_result' => $jodi,
                ]);
                Result::updateOrCreate([
                    'game_id' => $gameId,
                    'game_date' => $gameDate,
                    'type' => 'ah',
                ], ['number' => $ah,]);
                Result::updateOrCreate([
                    'game_id' => $gameId,
                    'game_date' => $gameDate,
                    'type' => 'bh',
                ], ['number' => $bh,]);
                Result::updateOrCreate([
                    'game_id' => $gameId,
                    'game_date' => $gameDate,
                    'type' => 'jodi',
                ], ['number' => $jodi,]);

                $stats = $this->settleGameBids(
                    game: $game,
                    gameId: $gameId,
                    gameDate: $gameDate,
                    jodi: $jodi,
                    ah: $ah,
                    bh: $bh
                );
                return ['game' => $game, 'stats' => $stats,];
            });
            $game = $settlement['game'];
            $stats = $settlement['stats'];
            $message = sprintf('Result for %s is %s', $game->name, $jodi);

            Notification::create([
                'user_id' => null,
                'subject' => 'Game Result Out',
                'message' => $message,
            ]);
            $this->sendResultNotification($game, $gameDate, $jodi);
            return redirect()->back()->with('success', "Result ({$jodi}) published for {$gameDate}. " . "{$stats['winners']} winner(s) credited.");
        } catch (\Throwable $e) {
            Log::error('Result settlement failed', [
                'game_id' => $gameId,
                'game_date' => $gameDate,
                'result' => $jodi,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Unable to publish result. Please try again.');
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
            'jodi' => [$jodi,],
            'haruf' => ['ander-' . $ah, 'bahar-' . $bh,],
        ];
        $bids = Bid::query()
            ->where('game_id', $gameId)
            ->where('game_date', $gameDate)
            ->where('status', 'pending')
            ->where(function ($query) use ($winningNumbers) {
                foreach ($winningNumbers as $type => $numbers) {
                    $query->orWhere(function ($query) use ($type, $numbers) {
                        $query->where('type', $type)->whereIn('number', $numbers);
                    });
                }
            })->lockForUpdate()->get();
        $winnerCount = 0;
        $totalWinningAmount = 0;
        foreach ($bids as $bid) {
            $user = User::query()->lockForUpdate()->find($bid->user_id);
            if (!$user) {
                continue;
            }
            $bidAmount = (float) $bid->amount;
            $winningAmount = $bid->type === 'jodi' ? $bidAmount * $reward : $bidAmount * ($reward / 10);
            $winningAmount = round($winningAmount, 2);
            $newBalance = round((float) $user->balance + $winningAmount, 2);
            $user->update(['balance' => $newBalance,]);

            $bid->update(['status' => 'win', 'winning_amount' => $winningAmount,]);

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
            ]);
            $winnerCount++;
            $totalWinningAmount += $winningAmount;
        }
        return ['winners' => $winnerCount, 'total_amount' => round($totalWinningAmount, 2),];
    }
    /** * Send FCM result notification. */
    private function sendResultNotification(Game $game, string $gameDate, string $result): void
    {
        try {
            Http::timeout(10)->withHeaders(['Authorization' => 'key=' . config('services.fcm.server_key'), 'Content-Type' => 'application/json',])->post(config('services.fcm.url'), ['to' => '/topics/weather', 'notification' => ['title' => $game->name . ' Result Published', 'body' => "Result for {$game->name} is {$result}",],]);
        } catch (\Throwable $e) {
            Log::error('FCM notification failed', ['game_id' => $game->id, 'game_date' => $gameDate, 'error' => $e->getMessage(),]);
        }
    }
    /** * Revert result and all settlements for a game/date. */
    public function revert(Request $request)
    {
        $validated = $request->validate(['game_id' => ['required', 'integer', 'exists:games,id',], 'game_date' => ['required', 'date_format:Y-m-d',],]);
        $gameId = (int) $validated['game_id'];
        $gameDate = $validated['game_date'];
        try {
            DB::transaction(function () use ($gameId, $gameDate) { /* * Lock winners so another settlement/revert * cannot process them simultaneously. */
                $winners = Winner::query()->where('game_id', $gameId)->where('game_date', $gameDate)->lockForUpdate()->get();
                foreach ($winners as $winner) {
                    $user = User::query()->lockForUpdate()->find($winner->user_id);
                    if (!$user) {
                        continue;
                    } /* * Reverse winning amount. */
                    $user->balance = round((float) $user->balance - (float) $winner->winning_amount, 2);
                    $user->save();
                    $previousResult = Result::query()
                        ->where('game_id', $gameId)
                        ->where('type', 'jodi')
                        ->where('game_date', '<', $gameDate)
                        ->latest('game_date')
                        ->value('number');

                    Game::query()
                        ->whereKey($gameId)
                        ->update([
                            'last_result' => $previousResult ?? 'Wait',
                        ]);

                    Bid::query()->whereKey($winner->bid_id)->update(['status' => 'pending', 'winning_amount' => 0,]);
                }
                Winner::query()->where('game_id', $gameId)->where('game_date', $gameDate)->delete(); /* * Delete result records. */
                Result::query()->where('game_id', $gameId)->where('game_date', $gameDate)->delete();
            });
            return redirect()->back()->with('success', 'Result reverted and winner amounts reversed successfully.');
        } catch (\Throwable $e) {
            Log::error('Result revert failed', ['game_id' => $gameId, 'game_date' => $gameDate, 'error' => $e->getMessage(),]);
            return redirect()->back()->with('error', 'Unable to revert result.');
        }
    }
}
