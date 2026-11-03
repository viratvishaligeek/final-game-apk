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
use App\Services\AppSettingsService;
use App\Services\Admin\GameResultSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ResultController extends Controller
{
    public function __construct(
        private AppSettingsService $settings,
        private GameResultSettlementService $settlement
    ) {}

    public function updateNotifications(Request $request)
    {
        $validated = $request->validate(['result_notifications_enabled' => ['required', 'boolean']]);
        $this->settings->save(['result_notifications_enabled' => $validated['result_notifications_enabled'] ? '1' : '0']);

        return redirect()->back()->with('success', 'Result notifications ' . ($validated['result_notifications_enabled'] ? 'enabled.' : 'disabled.'));
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
        ]);

        $selectedDate = $validated['date'] ?? now()->toDateString();
        $isCurrentCalendarDate = $selectedDate === now()->toDateString();
        $resultNotificationsEnabled = (string) $this->settings->value('result_notifications_enabled', '1') === '1';

        $allGames = Game::query()
            ->where('status', 'active')
            ->orderBy('serial')
            ->get();

        // The live result console normally uses today's calendar date. Disawar
        // is the exception: its active business day may still be yesterday
        // between midnight and its configured result-time boundary.
        $gameDates = $allGames->mapWithKeys(function (Game $game) use ($selectedDate, $isCurrentCalendarDate) {
            return [
                $game->id => $isCurrentCalendarDate
                    ? $game->businessDate()
                    : $selectedDate,
            ];
        });

        $existingResults = Result::query()
            ->where('type', 'jodi')
            ->whereIn('game_date', $gameDates->values()->unique()->all())
            ->get()
            ->filter(fn (Result $result) => (string) $result->game_date === (string) $gameDates->get($result->game_id))
            ->keyBy('game_id');

        return view('backend.result.index', compact(
            'allGames',
            'existingResults',
            'selectedDate',
            'gameDates',
            'isCurrentCalendarDate',
            'resultNotificationsEnabled',
        ));
    }

    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'game_id' => ['required', 'integer', 'exists:games,id'],
            'game_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'is_live_business_date' => ['sometimes', 'boolean'],
            'result' => ['required', 'digits:2'],
        ]);

        $gameId = (int) $validated['game_id'];
        $gameDate = $validated['game_date'];
        if (!empty($validated['is_live_business_date'])) {
            $game = Game::query()->findOrFail($gameId);
            $expectedBusinessDate = $game->businessDate();
            if ($gameDate !== $expectedBusinessDate) {
                return redirect()->back()->with(
                    'error',
                    'The live business date changed. Refresh the result console before declaring this result.'
                );
            }
        }
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
                    $this->settlement->reverseSettlement($game, $gameDate);
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

                $stats = $this->settlement->settleGameBids(
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

            // Settlement always completes first. This setting only controls result-related
            // notification generation and delivery; suppressed results are never replayed.
            if ((string) $this->settings->value('result_notifications_enabled', '1') === '1') {
                $message = sprintf('Result for %s is %s', $game->name, $jodi);
                $publicNotification = Notification::create([
                    'user_id' => null,
                    'subject' => 'Game Result Out',
                    'message' => $message,
                ]);
                $this->sendResultNotification($game, $gameDate, $jodi, $publicNotification->id);
                $this->sendWinnerNotifications($game, $gameDate);
            }

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
    private function sendResultNotification(Game $game, string $gameDate, string $result, int $notificationId): void
    {
        try {
            app(\App\Services\PushNotificationService::class)->sendPublicPush(
                $game->name . ' Result Published',
                "Result for {$game->name} is {$result}",
                $notificationId
            );
        } catch (\Throwable $e) {
            Log::error('FCM notification failed', [
                'game_id' => $game->id,
                'game_date' => $gameDate,
                'error' => $e->getMessage(),
            ]);
        }
    }


    /**
     * Send private reward alerts only to users who won in this settlement.
     * Run after the settlement transaction has committed.
     */
    private function sendWinnerNotifications(Game $game, string $gameDate): void
    {
        $totalsByUser = Winner::query()
            ->where('game_id', $game->id)
            ->where('game_date', $gameDate)
            ->selectRaw('user_id, SUM(winning_amount) as total_won')
            ->groupBy('user_id')
            ->get();

        foreach ($totalsByUser as $winner) {
            try {
                $user = User::query()->find($winner->user_id);
                if (!$user) {
                    continue;
                }

                $amount = number_format((float) $winner->total_won, 2);
                $title = 'Game Winning amount credited';
                $body = sprintf('You won ₹%s in %s for %s.', $amount, $game->name, $gameDate);

                $notification = Notification::query()->create([
                    'user_id' => $user->id,
                    'subject' => $title,
                    'message' => $body,
                ]);

                $accepted = app(\App\Services\PushNotificationService::class)
                    ->sendToUser($user, $title, $body, $notification->id);

                Log::info('Private game reward notification processed.', [
                    'game_id' => $game->id,
                    'game_date' => $gameDate,
                    'user_id' => $user->id,
                    'fcm_accepted_count' => $accepted,
                ]);
            } catch (\Throwable $exception) {
                Log::warning('Private game reward notification failed.', [
                    'game_id' => $game->id,
                    'game_date' => $gameDate,
                    'user_id' => $winner->user_id,
                    'error_class' => get_class($exception),
                    'error' => $exception->getMessage(),
                ]);
            }
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
