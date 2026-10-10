<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Game;
use App\Models\HomePage;
use App\Models\Page;
use App\Models\Result;
use App\Models\Winner;
use App\Services\AppSettingsService;
use App\Services\MonthlyChartService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MainController extends Controller
{
    public function index(Request $request, MonthlyChartService $charts)
    {
        $now = now()->timezone(config('app.timezone'));
        $availableMonths = $charts->availableMonths();
        $availableYears = array_map('intval', array_keys($availableMonths));
        $availableYears[] = (int) $now->format('Y');
        if ($request->filled('year') && filter_var($request->query('year'), FILTER_VALIDATE_INT)) {
            $requestedYear = (int) $request->query('year');
            if ($requestedYear >= 2000 && $requestedYear <= (int) $now->format('Y')) {
                $availableYears[] = $requestedYear;
            }
        }
        $availableYears = array_values(array_unique($availableYears));
        rsort($availableYears);

        $selectedYear = $request->filled('year') && filter_var($request->query('year'), FILTER_VALIDATE_INT)
            ? (int) $request->query('year')
            : (int) $now->format('Y');
        if ($selectedYear < 2000 || $selectedYear > (int) $now->format('Y')) {
            $selectedYear = (int) $now->format('Y');
        }

        if ($request->filled('month') && filter_var($request->query('month'), FILTER_VALIDATE_INT)) {
            $selectedMonth = (int) $request->query('month');
            if ($selectedMonth < 1 || $selectedMonth > 12) {
                $selectedMonth = (int) $now->format('n');
            }
        } elseif ($request->filled('year')) {
            $selectedMonth = ($availableMonths[$selectedYear][0] ?? null)
                ?: ($selectedYear === (int) $now->format('Y') ? (int) $now->format('n') : 1);
        } else {
            $selectedMonth = (int) $now->format('n');
        }

        $monthOptions = $availableMonths[$selectedYear] ?? [];
        if ($selectedYear === (int) $now->format('Y')) {
            $monthOptions[] = (int) $now->format('n');
        }
        $monthOptions[] = $selectedMonth;
        $monthOptions = array_values(array_unique($monthOptions));
        rsort($monthOptions);

        [$monthStart, $monthEnd, $monthDays] = $charts->monthDates($selectedYear, $selectedMonth);
        $games = Game::query()
            ->where('status', 'active')
            ->orderBy('serial')
            ->orderBy('id')
            ->get(['id', 'name', 'slug', 'serial', 'result_time', 'last_result']);

        $frontendResults = $this->getFrontendResults();
        $marketResults = collect($frontendResults)->map(fn (array $game) => collect($game)->only([
            'id', 'name', 'slug', 'time', 'today', 'yesterday',
        ])->all())->all();
        $recordYears = $availableYears;
        $homepageSections = HomePage::query()->where('status', 'active')->orderBy('id')->get();
        $faqs = Faq::query()->latest('id')->get();
        $chunkSize = $charts->chunkSize();
        $monthlyRows = $charts->homepageRows($games, $monthStart, $monthEnd, $monthDays);
        $monthlyEntries = $charts->paginate($monthlyRows, $request, $chunkSize);
        $monthLabel = $charts->monthLabel($selectedYear, $selectedMonth);

        // Public winner display is opt-in: names are never taken from private user
        // profile fields, and no amount or unverified claim is published.
        $topWinners = Winner::query()
            ->where('is_public', true)
            ->whereNotNull('public_display_name')
            ->where('public_display_name', '<>', '')
            ->with('game:id,name,slug')
            ->orderByDesc('game_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'game_id', 'public_display_name', 'number', 'type', 'game_date']);

        return view('frontend.index', compact(
            'frontendResults',
            'marketResults',
            'recordYears',
            'homepageSections',
            'faqs',
            'topWinners',
            'monthlyEntries',
            'monthLabel',
            'chunkSize',
            'availableMonths',
            'availableYears',
            'selectedYear',
            'selectedMonth',
            'monthOptions'
        ));
    }

    /**
     * Keep the established market URL working, but send visitors to the
     * monthly-only chart for the current application-timezone month.
     */
    public function market(string $slug)
    {
        $game = Game::query()->where('status', 'active')->where('slug', $slug)->firstOrFail();
        $now = now()->timezone(config('app.timezone'));

        return redirect()->route('frontend.chart', [
            'slug' => $game->slug,
            'year' => (int) $now->format('Y'),
            'month' => (int) $now->format('n'),
        ]);
    }

    public function chart(Request $request, string $slug, ?int $year = null, ?int $month = null, ?MonthlyChartService $charts = null)
    {
        $charts ??= app(MonthlyChartService::class);
        $game = Game::query()->where('status', 'active')->where('slug', $slug)->firstOrFail();
        $now = now()->timezone(config('app.timezone'));
        $availableMonths = $charts->availableMonths($game->id);

        $year ??= (int) $now->format('Y');
        abort_if($year < 2000 || $year > (int) $now->format('Y'), 404);

        if ($month === null) {
            $month = $year === (int) $now->format('Y')
                ? (int) $now->format('n')
                : (int) (($availableMonths[$year][0] ?? 1));
        }
        abort_if($month < 1 || $month > 12, 404);

        // Canonical monthly URLs always include both year and month. Legacy
        // /charts/{slug} and /charts/{slug}/{year} links remain valid.
        if (request()->route('year') === null || request()->route('month') === null) {
            return redirect()->route('frontend.chart', [
                'slug' => $game->slug,
                'year' => $year,
                'month' => $month,
            ]);
        }

        $availableYears = array_map('intval', array_keys($availableMonths));
        $availableYears[] = (int) $now->format('Y');
        $availableYears = array_values(array_unique($availableYears));
        rsort($availableYears);

        $monthOptions = $availableMonths[$year] ?? [];
        $monthOptions[] = $month;
        $monthOptions = array_values(array_unique($monthOptions));
        rsort($monthOptions);

        [$monthStart, $monthEnd, $monthDays] = $charts->monthDates($year, $month);
        $allRows = $charts->gameRows($game, $monthStart, $monthEnd, $monthDays);
        $hasPublishedResults = $allRows->isNotEmpty();
        $results = $charts->paginate($allRows, $request, $charts->chunkSize());
        $monthLabel = $charts->monthLabel($year, $month);
        $chunkSize = $charts->chunkSize();

        return view('frontend.chart', compact(
            'game',
            'year',
            'month',
            'monthLabel',
            'monthDays',
            'results',
            'availableMonths',
            'availableYears',
            'monthOptions',
            'hasPublishedResults',
            'chunkSize'
        ));
    }

    public function information(string $page)
    {
        $pageAliases = [
            'about' => 'about-us',
            'contact' => 'whatsapp',
            'terms-and-conditions' => 'terms-conditions',
        ];
        $databaseSlug = $pageAliases[$page] ?? $page;
        $databasePage = Page::query()->where('slug', $databaseSlug)->where('status', 'active')->first();
        if ($databasePage && $page !== 'faq') {
            return view('frontend.page', ['page' => $databasePage]);
        }

        $pages = [
            'about' => ['About this site', 'Play Online Khaiwal organizes stored Satta King results and historical monthly charts for reference. It does not publish predictions or guarantee future outcomes.'],
            'contact' => ['Contact', (string) app(AppSettingsService::class)->value('contact_address', 'For website or data-correction questions, use the contact channel configured by the site administrator.')],
            'faq' => ['Frequently asked questions', 'Use the market board to find published results. A dash means no result is available for the selected business date. Historical charts list stored records by market and month.'],
            'privacy-policy' => ['Privacy policy', 'This site should collect only the information needed to operate its features. Consult the site operator for details about deployment-specific logs, cookies, and retention practices.'],
            'terms-and-conditions' => ['Terms and conditions', 'Information is provided for reference only. Availability and correctness can vary; users are responsible for following applicable local laws.'],
            'disclaimer' => ['Disclaimer', (string) app(AppSettingsService::class)->value('disclaimer_content', 'Historical results are records, not predictions. No result or financial gain is guaranteed. Please follow applicable local laws and make responsible choices.')],
        ];
        abort_unless(isset($pages[$page]), 404);
        [$title, $copy] = $pages[$page];
        $faqs = $page === 'faq' ? Faq::query()->latest('id')->get() : collect();

        return view('frontend.information', compact('page', 'title', 'copy', 'faqs'));
    }

    public function showPage(string $slug)
    {
        $page = Page::query()->where('slug', $slug)->where('status', 'active')->firstOrFail();

        return view('frontend.page', compact('page'));
    }

    private function getFrontendResults(): array
    {
        $now = now()->timezone(config('app.timezone'));
        $games = Game::query()->where('status', 'active')->orderBy('serial')->orderBy('id')
            ->get(['id', 'name', 'slug', 'result_time', 'last_result']);
        $businessDates = $this->businessDatesFor($games, $now);
        $resultDates = collect($businessDates)->flatMap(fn (array $dates) => array_values($dates))->unique()->values();
        $results = Result::query()->whereIn('game_id', $games->pluck('id'))->where('type', 'jodi')
            ->whereIn('game_date', $resultDates)->get(['game_id', 'game_date', 'number'])->groupBy('game_id');

        return $games->map(function (Game $game) use ($results, $businessDates) {
            $gameResults = $results->get($game->id, collect());
            $dates = $businessDates[$game->id];
            $todayResult = optional($gameResults->firstWhere('game_date', $dates['today']))->number;
            $yesterdayResult = optional($gameResults->firstWhere('game_date', $dates['yesterday']))->number;

            return [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
                'time' => $game->result_time ? Carbon::parse($game->result_time)->format('h:i A') : '—',
                'today' => $todayResult !== null && $todayResult !== '' ? (string) $todayResult : '--',
                'yesterday' => $yesterdayResult !== null && $yesterdayResult !== '' ? (string) $yesterdayResult : '--',
                'last_result' => $game->last_result !== null && $game->last_result !== '' ? (string) $game->last_result : '--',
            ];
        })->values()->toArray();
    }

    private function businessDatesFor($games, Carbon $now): array
    {
        return $games->mapWithKeys(function (Game $game) use ($now) {
            $current = $game->businessDate($now);

            return [$game->id => [
                'today' => $current,
                'yesterday' => Carbon::parse($current, config('app.timezone'))->subDay()->toDateString(),
            ]];
        })->all();
    }
}
