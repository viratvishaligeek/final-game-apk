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
        // The homepage always shows the current calendar month in the configured
        // application timezone. Historical browsing belongs on each game's chart.
        $now = now()->timezone(config('app.timezone'));
        $selectedYear = (int) $now->format('Y');
        $selectedMonth = (int) $now->format('n');
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
        $recordYears = [$selectedYear];
        $homepageSections = HomePage::query()->where('status', 'active')->orderBy('id')->get();
        $faqs = Faq::query()->latest('id')->get();
        $chunkSize = $charts->chunkSize();
        $monthlyRows = $charts->homepageRows($games, $monthStart, $monthEnd, $monthDays);
        $hasMonthlyResults = $monthlyRows->contains(fn (array $row) => $row['status'] === 'published');
        $monthlyEntries = $charts->paginate($monthlyRows, $request, $chunkSize);
        $monthLabel = $charts->monthLabel($selectedYear, $selectedMonth);

        $homeCanonical = rtrim((string) app(AppSettingsService::class)->value('canonical_url', 'https://playonlinekhaiwal.com'), '/') . '/';
        $homeGraph = [[
            '@type' => 'WebPage',
            'name' => 'Play Online Khaiwal | Satta King Results & Monthly Charts',
            'url' => $homeCanonical,
            'description' => app(AppSettingsService::class)->value('meta_description', 'Satta King result records and monthly charts on Play Online Khaiwal.'),
        ]];
        if ($faqs->isNotEmpty()) {
            $homeGraph[] = [
                '@type' => 'FAQPage',
                'mainEntity' => $faqs->map(fn ($faq) => [
                    '@type' => 'Question',
                    'name' => strip_tags((string) $faq->question),
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags((string) $faq->answer)],
                ])->values()->all(),
            ];
        }
        $homeSchema = ['@context' => 'https://schema.org', '@graph' => $homeGraph];

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
            'frontendResults', 'marketResults', 'recordYears', 'homepageSections',
            'faqs', 'topWinners', 'monthlyEntries', 'hasMonthlyResults',
            'monthLabel', 'chunkSize', 'selectedYear', 'selectedMonth', 'homeSchema'
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

    public function chart(Request $request, string $slug, ?int $year = null, ?int $month = null)
    {
        $charts = app(MonthlyChartService::class);
        $game = Game::query()->where('status', 'active')->where('slug', $slug)->firstOrFail();
        $now = now()->timezone(config('app.timezone'));

        if ($year === null && $request->filled('year') && filter_var($request->query('year'), FILTER_VALIDATE_INT)) {
            $year = (int) $request->query('year');
        }
        if ($month === null && $request->filled('month') && filter_var($request->query('month'), FILTER_VALIDATE_INT)) {
            $month = (int) $request->query('month');
        }

        $year ??= (int) $now->format('Y');
        abort_if($year < 2000 || $year > (int) $now->format('Y'), 404);
        $month ??= $year === (int) $now->format('Y')
            ? (int) $now->format('n')
            : (int) ($charts->availableMonths($game->id)[$year][0] ?? 0);
        abort_if($month < 1 || $month > 12, 404);
        abort_if($year === (int) $now->format('Y') && $month > (int) $now->format('n'), 404);

        // A legitimate older month must contain stored records. Do not publish
        // indexable empty archive URLs merely because the route parameters parse.
        if ($year < (int) $now->format('Y') || $month < (int) $now->format('n')) {
            abort_unless(Result::query()->where('game_id', $game->id)->where('type', 'jodi')
                ->whereBetween('game_date', [sprintf('%04d-%02d-01', $year, $month), $now->copy()->setDate($year, $month, 1)->endOfMonth()->toDateString()])
                ->whereNotNull('number')->where('number', '<>', '')->exists(), 404);
        }

        if ($request->route('year') === null || $request->route('month') === null) {
            return redirect()->route('frontend.chart', ['slug' => $game->slug, 'year' => $year, 'month' => $month], 301);
        }

        $availableMonths = $charts->availableMonths($game->id);
        $availableYears = array_map('intval', array_keys($availableMonths));
        $availableYears[] = $year;
        $availableYears[] = (int) $now->format('Y');
        $availableYears = array_values(array_unique($availableYears));
        rsort($availableYears);

        $monthOptions = $availableMonths[$year] ?? [];
        $monthOptions[] = $month;
        $monthOptions = array_values(array_unique($monthOptions));
        rsort($monthOptions);

        [$monthStart, $monthEnd, $monthDays] = $charts->monthDates($year, $month);
        $allRows = $charts->gameRows($game, $monthStart, $monthEnd, $monthDays);
        $hasPublishedResults = $allRows->contains(fn (array $row) => $row['status'] === 'published');
        $results = $charts->paginate($allRows, $request, $charts->chunkSize());
        $monthLabel = $charts->monthLabel($year, $month);
        $chunkSize = $charts->chunkSize();

        return view('frontend.chart', compact(
            'game', 'year', 'month', 'monthLabel', 'monthDays', 'results',
            'availableMonths', 'availableYears', 'monthOptions', 'hasPublishedResults', 'chunkSize'
        ));
    }

    /**
     * Full-year view for a single game. Only the selected year's saved result
     * rows are loaded; all twelve month columns are generated in memory.
     */
    public function legacyChart(string $year, string $month, string $slug)
    {
        $now = now()->timezone(config('app.timezone'));
        $yearNumber = (int) $year;
        $monthNumber = (int) $month;
        abort_if($yearNumber < 2000 || $yearNumber > (int) $now->format('Y') || $monthNumber < 1 || $monthNumber > 12, 404);
        abort_if($yearNumber === (int) $now->format('Y') && $monthNumber > (int) $now->format('n'), 404);

        $game = Game::query()->where('status', 'active')->where('slug', $slug)->firstOrFail();
        if ($yearNumber < (int) $now->format('Y') || $monthNumber < (int) $now->format('n')) {
            $start = sprintf('%04d-%02d-01', $yearNumber, $monthNumber);
            $end = Carbon::createFromDate($yearNumber, $monthNumber, 1, config('app.timezone'))->endOfMonth()->toDateString();
            abort_unless(Result::query()->where('game_id', $game->id)->where('type', 'jodi')
                ->whereBetween('game_date', [$start, $end])->whereNotNull('number')->where('number', '<>', '')->exists(), 404);
        }

        return redirect()->route('frontend.chart', [
            'slug' => $game->slug, 'year' => $yearNumber, 'month' => $monthNumber,
        ], 301);
    }

    public function yearChart(string $slug, int $year)
    {
        $game = Game::query()->where('status', 'active')->where('slug', $slug)->firstOrFail();
        $now = now()->timezone(config('app.timezone'));
        abort_if($year < 2000 || $year > (int) $now->format('Y'), 404);

        $start = Carbon::createFromDate($year, 1, 1, config('app.timezone'))->startOfYear();
        $end = $year === (int) $now->format('Y')
            ? $now->copy()->endOfDay()
            : $start->copy()->endOfYear();
        $saved = Result::query()->where('game_id', $game->id)->where('type', 'jodi')
            ->whereBetween('game_date', [$start->toDateString(), $end->toDateString()])
            ->whereNotNull('number')->where('number', '<>', '')
            ->get(['game_date', 'number'])
            ->keyBy(fn (Result $result) => Carbon::parse($result->game_date, config('app.timezone'))->format('n-j'));

        if ($year < (int) $now->format('Y')) {
            abort_if($saved->isEmpty(), 404);
        }

        $maxDays = 31;
        $yearRows = collect(range(1, $maxDays))->map(function (int $day) use ($year, $saved) {
            $cells = [];
            for ($month = 1; $month <= 12; $month++) {
                $daysInMonth = Carbon::createFromDate($year, $month, 1, config('app.timezone'))->daysInMonth;
                $cells[$month] = $day <= $daysInMonth
                    ? optional($saved->get($month . '-' . $day))->number
                    : null;
            }

            return ['day' => $day, 'cells' => $cells];
        });

        $canonical = rtrim((string) app(AppSettingsService::class)->value('canonical_url', 'https://playonlinekhaiwal.com'), '/')
            . '/charts/' . $game->slug . '/year/' . $year;

        return view('frontend.year-chart', [
            'game' => $game,
            'year' => $year,
            'yearRows' => $yearRows,
            'hasPublishedResults' => $saved->isNotEmpty(),
            'canonical' => $canonical,
        ]);
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
        $informationAliases = [
            'about-us' => 'about',
            'whatsapp' => 'contact',
            'terms-conditions' => 'terms-and-conditions',
            'privacy-policy' => 'privacy-policy',
            'disclaimer' => 'disclaimer',
            'faq' => 'faq',
        ];

        if (isset($informationAliases[$slug])) {
            return redirect()->route('information', ['page' => $informationAliases[$slug]], 301);
        }

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
