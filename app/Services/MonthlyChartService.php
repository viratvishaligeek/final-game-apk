<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Result;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonthlyChartService
{
    public function chunkSize(): int
    {
        $size = filter_var(app(AppSettingsService::class)->value('chart_chunk_size', 10), FILTER_VALIDATE_INT);

        return $size !== false && $size >= 1 && $size <= 50 ? $size : 10;
    }

    /**
     * Return available months as year => [month numbers, newest first].
     * Only distinct dates are read from the historical table.
     */
    public function availableMonths(?int $gameId = null): array
    {
        $driver = DB::connection()->getDriverName();
        $monthExpression = $driver === 'sqlite'
            ? "strftime('%Y-%m', game_date)"
            : "DATE_FORMAT(game_date, '%Y-%m')";

        $keys = Result::query()
            ->where('type', 'jodi')
            ->whereNotNull('game_date')
            ->whereHas('game', fn ($query) => $query->where('status', 'active'))
            ->when($gameId !== null, fn ($query) => $query->where('game_id', $gameId))
            ->selectRaw("{$monthExpression} as month_key")
            ->distinct()
            ->orderByDesc('month_key')
            ->pluck('month_key');

        $available = [];
        foreach ($keys as $key) {
            if (!is_string($key) || !preg_match('/^(\d{4})-(\d{2})$/', $key, $match)) {
                continue;
            }

            $year = (int) $match[1];
            $month = (int) $match[2];
            if ($month >= 1 && $month <= 12) {
                $available[$year] ??= [];
                $available[$year][] = $month;
            }
        }

        foreach ($available as &$months) {
            $months = array_values(array_unique($months));
            rsort($months);
        }
        unset($months);
        krsort($available);

        return $available;
    }

    public function monthDates(int $year, int $month): array
    {
        $start = Carbon::createFromDate($year, $month, 1, config('app.timezone'))->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $dates = [];

        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $dates[] = $day->copy();
        }

        return [$start, $end, $dates];
    }

    /**
     * Homepage: one row per active game and calendar day for the selected
     * month. Current-month missing/future cells are explicitly labelled.
     */
    public function homepageRows(Collection $games, Carbon $start, Carbon $end, array $dates): Collection
    {
        $results = Result::query()
            ->whereIn('game_id', $games->pluck('id'))
            ->where('type', 'jodi')
            ->whereBetween('game_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('game_date')
            ->get(['game_id', 'game_date', 'number'])
            ->keyBy(fn (Result $result) => $result->game_id . '|' . $result->game_date);

        $now = now()->timezone(config('app.timezone'));
        $rows = collect();

        foreach ($dates as $date) {
            foreach ($games as $game) {
                $result = $results->get($game->id . '|' . $date->toDateString());
                $number = $result && filled($result->number) ? (string) $result->number : null;
                $status = $number !== null
                    ? 'published'
                    : ($date->gt($now->copy()->startOfDay()) ? 'upcoming' : 'pending');

                $rows->push([
                    'game' => $game,
                    'date' => $date->copy(),
                    'number' => $number,
                    'status' => $status,
                ]);
            }
        }

        return $rows;
    }

    /**
     * Dedicated game chart: only query selected-month results; return no rows
     * when the month has no published records for this game.
     */
    public function gameRows(Game $game, Carbon $start, Carbon $end, array $dates): Collection
    {
        $results = Result::query()
            ->where('game_id', $game->id)
            ->where('type', 'jodi')
            ->whereBetween('game_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('game_date')
            ->get(['game_date', 'number'])
            ->keyBy(fn (Result $result) => Carbon::parse($result->game_date, config('app.timezone'))->toDateString());

        if ($results->filter(fn (Result $result) => filled($result->number))->isEmpty()) {
            return collect();
        }

        $now = now()->timezone(config('app.timezone'));

        return collect($dates)->map(function (Carbon $date) use ($results, $game, $now) {
            $result = $results->get($date->toDateString());
            $number = $result && filled($result->number) ? (string) $result->number : null;

            return [
                'game' => $game,
                'date' => $date->copy(),
                'number' => $number,
                'status' => $number !== null
                    ? 'published'
                    : ($date->gt($now->copy()->startOfDay()) ? 'upcoming' : 'pending'),
            ];
        });
    }

    public function paginate(Collection $rows, Request $request, ?int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? $this->chunkSize();
        $page = max(1, LengthAwarePaginator::resolveCurrentPage('page'));
        $items = $rows->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $rows->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'page',
            ]
        );
    }

    public function monthLabel(int $year, int $month): string
    {
        return Carbon::createFromDate($year, $month, 1, config('app.timezone'))->translatedFormat('F Y');
    }
}
