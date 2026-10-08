<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Result;
use Carbon\Carbon;

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
        $today = Carbon::today();
        $yesterday = $today->copy()->subDay();

        $games = Game::query()
            ->where('status', 'active')
            ->orderBy('serial')
            ->get([
                'id',
                'name',
                'slug',
                'result_time',
                'last_result',
            ]);
        $dates = [
            'today' => $today->toDateString(),
            'yesterday' => $yesterday->toDateString(),
        ];

        $results = Result::query()
            ->whereIn('game_id', $games->pluck('id'))
            ->where('type', 'jodi')
            ->whereIn('game_date', $dates)
            ->get([
                'game_id',
                'game_date',
                'number',
            ])
            ->groupBy('game_id');

        return $games->map(function ($game) use ($results, $dates) {
            $gameResults = $results->get($game->id, collect());

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
        $today = Carbon::today();
        $yesterday = $today->copy()->subDay();

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

        $results = Result::query()
            ->whereIn('game_id', $games->pluck('id'))
            ->where('type', 'jodi')
            ->whereIn('game_date', [
                $today->toDateString(),
                $yesterday->toDateString(),
            ])
            ->get([
                'game_id',
                'game_date',
                'number',
            ])
            ->groupBy('game_id');

        return $games->map(function ($game) use ($results, $today, $yesterday) {
            $gameResults = $results->get($game->id, collect());

            $todayResult = optional(
                $gameResults->firstWhere(
                    'game_date',
                    $today->toDateString()
                )
            )->number;

            $yesterdayResult = optional(
                $gameResults->firstWhere(
                    'game_date',
                    $yesterday->toDateString()
                )
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
    private function getRecordYears(): array
    {
        return Result::query()
            ->where('type', 'jodi')
            ->whereNotNull('game_date')
            ->selectRaw('YEAR(game_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn($year) => (int) $year)
            ->values()
            ->toArray();
    }
}
