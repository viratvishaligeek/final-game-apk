<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Result;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResultChartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'game_id' => ['nullable', 'integer', 'exists:games,id'],
        ]);

        $today = Carbon::today();

        $month = (int) ($validated['month'] ?? $today->month);
        $year = (int) ($validated['year'] ?? $today->year);
        $gameId = isset($validated['game_id']) ? (int) $validated['game_id'] : null;

        $games = Game::query()
            ->where('status', 'active')
            ->when(
                $gameId,
                fn ($query) => $query->whereKey($gameId)
            )
            ->orderBy('serial')
            ->orderBy('id')
            ->get([
                'id',
                'name',
                'slug',
                'last_result',
                'serial',
            ]);

        if ($games->isEmpty()) {
            return response()->json([
                'success' => true,
                'month' => $month,
                'year' => $year,
                'games' => [],
                'data' => [],
            ]);
        }

        $startDate = Carbon::create($year, $month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth();

        $results = Result::query()
            ->whereIn('game_id', $games->pluck('id'))
            ->where('type', 'jodi')
            ->whereBetween('game_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->get([
                'game_id',
                'game_date',
                'number',
            ])
            ->keyBy(
                fn ($result) => $result->game_id . '_' . $result->game_date
            );

        $data = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateString = $date->toDateString();
            $isFuture = $date->gt($today);
            $isToday = $date->isSameDay($today);

            $rowResults = [];

            foreach ($games as $game) {
                $result = $isFuture
                    ? null
                    : $results->get($game->id . '_' . $dateString);

                $rowResults[(string) $game->id] = $result?->number;
            }

            $data[] = [
                'date' => $dateString,
                'formatted_day' => $date->format('d M'),
                'is_today' => $isToday,
                'results' => $rowResults,
            ];
        }

        return response()->json([
            'success' => true,
            'month' => $month,
            'year' => $year,
            'games' => $games->map(fn ($game) => [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
                'last_result' => $game->last_result,
                'serial' => $game->serial,
            ])->values(),
            'data' => $data,
        ]);
    }
}
