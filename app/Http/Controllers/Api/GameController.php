<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Game;
use App\Models\Result;
use App\Services\AppSettingsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function __construct(
        protected AppSettingsService $appSettings
    ) {}

    public function index(Request $request)
    {
        $timezone = config('app.timezone');

        /*
        |--------------------------------------------------------------------------
        | Server Time
        |--------------------------------------------------------------------------
        */

        $now = now()->timezone($timezone);

        $today = $now->toDateString();
        $yesterday = $now->copy()->subDay()->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Games
        |--------------------------------------------------------------------------
        |
        | Only active games are shown.
        |
        */

        $games = Game::query()
            ->orderBy('serial')
            ->orderBy('id')
            ->get([
                'id',
                'name',
                'slug',
                'play_start',
                'play_end',
                'result_time',
                'status',
                'serial',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Results - one query instead of N+1
        |--------------------------------------------------------------------------
        */

        $gameIds = $games->pluck('id');

        // Homepage "today" is the application's calendar date, not a market-specific
        // business-day boundary. Keep businessDate() for play/betting logic only.
        $gameDates = $games->mapWithKeys(function (Game $game) use ($today, $yesterday) {
            return [
                $game->id => [
                    'current' => $today,
                    'previous' => $yesterday,
                ],
            ];
        });

        $resultDates = $gameDates
            ->flatMap(fn (array $dates) => array_values($dates))
            ->unique()
            ->values();

        $results = collect();

        if ($gameIds->isNotEmpty()) {
            $results = Result::query()
                ->whereIn('game_id', $gameIds)
                ->where('type', 'jodi')
                ->whereIn('game_date', $resultDates)
                ->orderByDesc('id')
                ->get([
                    'id',
                    'game_id',
                    'number',
                    'game_date',
                    'type',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Group results by game/date
        |--------------------------------------------------------------------------
        */

        $resultsByGame = $results
            ->groupBy('game_id');

        /*
        |--------------------------------------------------------------------------
        | Transform Games
        |--------------------------------------------------------------------------
        */

        $games = $games->map(function (Game $game) use (
            $resultsByGame,
            $gameDates,
            $now
        ) {
            $gameResults = $resultsByGame->get($game->id, collect());
            $businessDates = $gameDates->get($game->id);
            $businessDate = $businessDates['current'];
            $previousBusinessDate = $businessDates['previous'];

            $todayResult = $gameResults
                ->where('game_date', $businessDate)
                ->sortByDesc('id')
                ->first();

            $yesterdayResult = $gameResults
                ->where('game_date', $previousBusinessDate)
                ->sortByDesc('id')
                ->first();

            $window = $game->getPlayWindow($now);

            $isPlayable = $game->isPlayableAt($now);

            $remainingSeconds = $game->remainingSeconds($now);

            return [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,

                'play_start' => $game->play_start,
                'play_end' => $game->play_end,
                'result_time' => $game->result_time,
                'business_date' => $businessDate,

                'status' => $game->status,
                'serial' => $game->serial,

                /*
                |--------------------------------------------------------------------------
                | Current Result
                |--------------------------------------------------------------------------
                */

                'latest_result' => $todayResult ? [
                    'id' => $todayResult->id,
                    'number' => $todayResult->number,
                    'date' => $todayResult->game_date,
                    'type' => $todayResult->type,
                ] : null,

                /*
                |--------------------------------------------------------------------------
                | Previous Result
                |--------------------------------------------------------------------------
                */

                'previous_result' => $yesterdayResult ? [
                    'id' => $yesterdayResult->id,
                    'number' => $yesterdayResult->number,
                    'date' => $yesterdayResult->game_date,
                    'type' => $yesterdayResult->type,
                ] : null,

                /*
                |--------------------------------------------------------------------------
                | Compatibility
                |--------------------------------------------------------------------------
                */

                'today_result' => $todayResult ? [
                    'id' => $todayResult->id,
                    'number' => $todayResult->number,
                    'date' => $todayResult->game_date,
                    'type' => $todayResult->type,
                ] : null,

                'last_result' => $todayResult?->number,

                /*
                |--------------------------------------------------------------------------
                | Play State
                |--------------------------------------------------------------------------
                */

                'is_playable' => $isPlayable,

                'play_status' => $isPlayable
                    ? 'running'
                    : 'closed',

                'monthly_closure' => $game->isMonthlyClosureDate($now),
                'play_status_message' => $game->isMonthlyClosureDate($now)
                    ? $game->monthlyClosureMessage()
                    : ($game->status !== 'active'
                        ? 'This game is currently inactive.'
                        : (!$isPlayable ? 'Betting time for this game is closed.' : null)),

                'remaining_seconds' => $remainingSeconds,

                /*
                |--------------------------------------------------------------------------
                | Absolute timestamps
                |--------------------------------------------------------------------------
                |
                | Frontend countdown can use these.
                |
                */

                'play_window_start_at' => $window
                    ? $window['start']->toIso8601String()
                    : null,

                'play_window_end_at' => $window
                    ? $window['end']->toIso8601String()
                    : null,
            ];
        })->sort(function ($a, $b) use ($now) {
            $rank = function ($game) use ($now) {
                if (($game['status'] ?? null) !== 'active') return 2;
                if (!empty($game['is_playable'])) return 0;
                $start = $game['play_window_start_at'] ? Carbon::parse($game['play_window_start_at']) : null;
                return $start && $start->greaterThan($now) ? 1 : 2;
            };
            $rankCompare = $rank($a) <=> $rank($b);
            if ($rankCompare !== 0) return $rankCompare;
            if ($rank($a) === 1) {
                return strcmp((string) $a['play_window_start_at'], (string) $b['play_window_start_at']);
            }
            return ((int) ($a['serial'] ?? 0)) <=> ((int) ($b['serial'] ?? 0));
        })->values();

        /*
        |--------------------------------------------------------------------------
        | Featured Game
        |--------------------------------------------------------------------------
        |
        | Priority:
        | 1. Today's result
        | 2. Currently running game
        | 3. First active game
        |
        */

        $featuredGame = $games
            ->filter(fn($game) => !empty($game['today_result']))
            ->sortByDesc(function ($game) {
                return sprintf(
                    '%s %s',
                    $game['today_result']['date'] ?? '',
                    $game['result_time'] ?? '00:00:00'
                );
            })
            ->first();

        if (!$featuredGame) {
            $featuredGame = $games
                ->firstWhere('is_playable', true);
        }

        if (!$featuredGame) {
            $featuredGame = $games->firstWhere('status', 'active') ?? $games->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Banners
        |--------------------------------------------------------------------------
        */

        $banners = Banner::query()
            ->where('status', 'active')
            ->orderByDesc('id')
            ->get([
                'id',
                'name',
                'image',
            ])
            ->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'name' => $banner->name,
                    'title' => $banner->name,
                    'image' => $banner->image,
                    'image_url' => $banner->image_url ?? $banner->image,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Notice / Marquee
        |--------------------------------------------------------------------------
        */

        $settings = $this->appSettings->all();
        $noticeStatus = $settings['notice_status'] ?? 'inactive';

        $notice = [
            'status' => $noticeStatus === 'active',
            'content' => $settings['admin_notice'] ?? null,
        ];

        $marquee = [
            'content' => $settings['marquee'] ?? null,
        ];

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'status' => true,
            'message' => 'Games fetched successfully',

            'server_time' => $now->toIso8601String(),
            'timezone' => $timezone,

            'today' => $today,
            'yesterday' => $yesterday,

            'banners' => $banners,

            'notice' => $notice,

            'marquee' => $marquee,

            'featured_game' => $featuredGame,

            'market_count' => $games->count(),

            'games' => $games,
        ]);
    }

    public function show(Game $game): JsonResponse
    {
        if ($game->status !== 'active') {
            return response()->json([
                'status' => false,
                'message' => 'This game is currently inactive.',
            ], 404);
        }

        $now = now()->timezone(config('app.timezone'));

        $window = $game->getPlayWindow($now);

        $isPlayable = $game->isPlayableAt($now);

        return response()->json([
            'status' => true,
            'message' => 'Game fetched successfully',

            'server_time' => $now->toIso8601String(),
            'timezone' => config('app.timezone'),

            'data' => [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,

                'result_time' => $game->result_time,
                'business_date' => $game->businessDate($now),

                'play_start' => $game->play_start,
                'play_end' => $game->play_end,

                'status' => $game->status,
                'serial' => $game->serial,

                'last_result' => $game->last_result,

                'is_playable' => $isPlayable,

                'play_status' => $isPlayable
                    ? 'running'
                    : 'closed',

                'monthly_closure' => $game->isMonthlyClosureDate($now),
                'play_status_message' => $game->isMonthlyClosureDate($now)
                    ? $game->monthlyClosureMessage()
                    : (!$isPlayable ? 'Betting time for this game is closed.' : null),

                'remaining_seconds' => $game->remainingSeconds($now),

                'play_window_start_at' => $window
                    ? $window['start']->toIso8601String()
                    : null,

                'play_window_end_at' => $window
                    ? $window['end']->toIso8601String()
                    : null,
            ],
        ]);
    }
}
