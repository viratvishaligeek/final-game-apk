<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Result;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MainController extends Controller
{
    public function index()
    {
        $frontendResults = $this->getFrontendResults();
        $marketResults = $this->getMarketResults();
        $recordYears = $this->getRecordYears();

        return view('frontend.index', compact(
            'frontendResults',
            'marketResults',
            'recordYears'
        ));
    }


    private function getFrontendResults(): array
    {
        $now = now()->timezone(config('app.timezone'));

        $games = Game::query()
            ->where('status', 'active')
            ->orderBy('serial')
            ->orderBy('id')
            ->get([
                'id',
                'name',
                'slug',
                'result_time',
                'last_result',
            ]);

        $businessDates = $this->businessDatesFor($games, $now);
        $resultDates = collect($businessDates)
            ->flatMap(fn (array $dates) => array_values($dates))
            ->unique()
            ->values();

        $results = Result::query()
            ->whereIn('game_id', $games->pluck('id'))
            ->where('type', 'jodi')
            ->whereIn('game_date', $resultDates)
            ->get([
                'game_id',
                'game_date',
                'number',
            ])
            ->groupBy('game_id');

        return $games->map(function ($game) use ($results, $businessDates) {
            $gameResults = $results->get($game->id, collect());
            $dates = $businessDates[$game->id];

            $todayResult = optional(
                $gameResults->firstWhere('game_date', $dates['today'])
            )->number;

            $yesterdayResult = optional(
                $gameResults->firstWhere('game_date', $dates['yesterday'])
            )->number;

            return [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
                'time' => Carbon::parse($game->result_time)->format('h:i A'),
                'today' => $todayResult ?: '--',
                'yesterday' => $yesterdayResult ?: '--',
                'last_result' => $game->last_result ?: '--',
            ];
        })->values()->toArray();
    }

    private function getMarketResults(): array
    {
        $now = now()->timezone(config('app.timezone'));

        $games = Game::query()
            ->where('status', 'active')
            ->orderBy('serial')
            ->orderBy('id')
            ->get([
                'id',
                'name',
                'slug',
                'result_time',
            ]);

        $businessDates = $this->businessDatesFor($games, $now);
        $resultDates = collect($businessDates)
            ->flatMap(fn (array $dates) => array_values($dates))
            ->unique()
            ->values();

        $results = Result::query()
            ->whereIn('game_id', $games->pluck('id'))
            ->where('type', 'jodi')
            ->whereIn('game_date', $resultDates)
            ->get([
                'game_id',
                'game_date',
                'number',
            ])
            ->groupBy('game_id');

        return $games->map(function ($game) use ($results, $businessDates) {
            $gameResults = $results->get($game->id, collect());
            $dates = $businessDates[$game->id];

            $todayResult = optional(
                $gameResults->firstWhere('game_date', $dates['today'])
            )->number;

            $yesterdayResult = optional(
                $gameResults->firstWhere('game_date', $dates['yesterday'])
            )->number;

            return [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
                'time' => Carbon::parse($game->result_time)->format('h:i A'),
                'today' => $todayResult ?: '--',
                'yesterday' => $yesterdayResult ?: '--',
            ];
        })->values()->toArray();
    }

    /**
     * Return current and previous business dates per game.
     */
    private function businessDatesFor($games, Carbon $now): array
    {
        return $games->mapWithKeys(function (Game $game) use ($now) {
            $current = $game->businessDate($now);

            return [
                $game->id => [
                    'today' => $current,
                    'yesterday' => Carbon::parse($current, config('app.timezone'))
                        ->subDay()
                        ->toDateString(),
                ],
            ];
        })->all();
    }

    private function getRecordYears(): array
    {
        $yearExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%Y', game_date) AS INTEGER)"
            : 'YEAR(game_date)';

        return Result::query()
            ->where('type', 'jodi')
            ->whereNotNull('game_date')
            ->selectRaw("{$yearExpression} as year")
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->values()
            ->toArray();
    }
}
