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


    public function market(string $slug)
    {
        $game = Game::query()->where('status', 'active')->where('slug', $slug)->firstOrFail();
        $yearExpression = DB::connection()->getDriverName() === 'sqlite' ? "CAST(strftime('%Y', game_date) AS INTEGER)" : 'YEAR(game_date)';
        $years = Result::query()->where('game_id', $game->id)->where('type', 'jodi')->whereNotNull('game_date')
            ->selectRaw("{$yearExpression} as year")->distinct()->orderByDesc('year')->pluck('year')->map(fn ($year) => (int) $year)->values();
        $results = Result::query()->where('game_id', $game->id)->where('type', 'jodi')->whereNotNull('game_date')
            ->orderByDesc('game_date')->paginate(60);
        return view('frontend.market', compact('game', 'years', 'results'));
    }

    public function chart(string $slug, ?int $year = null)
    {
        $game = Game::query()->where('status', 'active')->where('slug', $slug)->firstOrFail();
        $year = $year ?: (int) now()->format('Y');
        $query = Result::query()->where('game_id', $game->id)->where('type', 'jodi')->whereYear('game_date', $year)->orderByDesc('game_date');
        $results = $query->paginate(100)->withQueryString();
        $yearExpression = DB::connection()->getDriverName() === 'sqlite' ? "CAST(strftime('%Y', game_date) AS INTEGER)" : 'YEAR(game_date)';
        $years = Result::query()->where('game_id', $game->id)->where('type', 'jodi')->whereNotNull('game_date')
            ->selectRaw("{$yearExpression} as year")->distinct()->orderByDesc('year')->pluck('year')->map(fn ($item) => (int) $item)->values();
        return view('frontend.chart', compact('game', 'year', 'years', 'results'));
    }

    public function information(string $page)
    {
        $pages = [
            'about' => ['About this site', 'This site organizes published market results and historical records for reference. It does not publish predictions or guarantee future outcomes.'],
            'contact' => ['Contact', 'For website or data-correction questions, use the contact channel configured by the site administrator. No unconfigured personal contact details are published here.'],
            'faq' => ['Frequently asked questions', 'Use the market board to find published results. A dash means no result is available for the selected business date. Historical charts list stored records by market and year.'],
            'privacy-policy' => ['Privacy policy', 'This site should collect only the information needed to operate its features. Consult the site operator for details about deployment-specific logs, cookies, and retention practices.'],
            'terms-and-conditions' => ['Terms and conditions', 'Information is provided for reference only. Availability and correctness can vary; users are responsible for following applicable local laws.'],
            'disclaimer' => ['Disclaimer', 'Historical results are records, not predictions. No result or financial gain is guaranteed. Please follow applicable local laws and make responsible choices.'],
        ];
        abort_unless(isset($pages[$page]), 404);
        [$title, $copy] = $pages[$page];
        return view('frontend.information', compact('page', 'title', 'copy'));
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
                'today' => $todayResult !== null && $todayResult !== '' ? (string) $todayResult : '--',
                'yesterday' => $yesterdayResult !== null && $yesterdayResult !== '' ? (string) $yesterdayResult : '--',
                'last_result' => $game->last_result !== null && $game->last_result !== '' ? (string) $game->last_result : '--',
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
