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
        $selectedYear = (int) $now->format('Y');
        $selectedMonth = (int) $now->format('n');
        [$monthStart, $monthEnd, $monthDays] = $charts->monthDates($selectedYear, $selectedMonth);
        $games = Game::query()
            ->where('status', 'active')
            ->orderBy('serial')
            ->orderBy('id')
            ->get(['id', 'name', 'slug', 'serial', 'result_time', 'last_result']);

        // Keep public lists in stable serial order, but feature the game whose
        // latest non-empty result record was published/updated most recently.
        $frontendResults = $this->getFrontendResults();
        $featuredGame = collect($frontendResults)->sort(function (array $a, array $b) {
            $timestampOrder = strcmp((string) ($b['latest_result_at'] ?? ''), (string) ($a['latest_result_at'] ?? ''));

            return $timestampOrder !== 0
                ? $timestampOrder
                : ((int) $a['serial'] <=> (int) $b['serial']);
        })->first();
        $featuredGameId = $featuredGame['id'] ?? null;
        $remainingFrontendResults = collect($frontendResults)
            ->reject(fn(array $game) => $featuredGameId !== null && (int) $game['id'] === (int) $featuredGameId)
            ->values()
            ->all();
        $marketResults = collect($remainingFrontendResults)->map(fn(array $game) => collect($game)->only([
            'id',
            'name',
            'slug',
            'serial',
            'time',
            'today',
            'yesterday',
        ])->all())->all();
        $recordYears = [$selectedYear];
        $homepageSections = HomePage::query()->where('status', 'active')->orderBy('id')->get();
        $faqs = Faq::query()->latest('id')->get();
        $chunkSize = $charts->chunkSize();
        $monthlyTables = $charts->homepageTables($games, $monthStart, $monthEnd, $monthDays, $chunkSize);
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
                'mainEntity' => $faqs->map(fn($faq) => [
                    '@type' => 'Question',
                    'name' => strip_tags((string) $faq->question),
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags((string) $faq->answer)],
                ])->values()->all(),
            ];
        }
        $homeSchema = ['@context' => 'https://schema.org', '@graph' => $homeGraph];

        return view('frontend.index', compact(
            'frontendResults',
            'games',
            'remainingFrontendResults',
            'featuredGame',
            'marketResults',
            'recordYears',
            'homepageSections',
            'faqs',
            'monthlyTables',
            'monthLabel',
            'chunkSize',
            'selectedYear',
            'selectedMonth',
            'homeSchema'
        ));
    }

    public function market(Request $request, string $slug, MonthlyChartService $charts)
    {
        $game = Game::query()->where('status', 'active')->where('slug', $slug)->firstOrFail();
        $now = now()->timezone(config('app.timezone'));
        $year = (int) $now->format('Y');
        $month = (int) $now->format('n');

        if ($request->query('year') !== null || $request->query('month') !== null) {
            $queryData = $request->validate([
                'year' => ['sometimes', 'required', 'regex:/^[0-9]{4}$/'],
                'month' => ['sometimes', 'required', 'integer', 'between:1,12'],
            ]);
            $year = (int) ($queryData['year'] ?? $year);
            $month = (int) ($queryData['month'] ?? $month);
            abort_if($year < 2000 || $year > (int) $now->format('Y'), 404);
            abort_if($year === (int) $now->format('Y') && $month > (int) $now->format('n'), 404);
        }

        [$monthStart, $monthEnd, $monthDays] = $charts->monthDates($year, $month);
        $results = $charts->gameRows($game, $monthStart, $monthEnd, $monthDays);
        $monthLabel = $charts->monthLabel($year, $month);
        $currentYear = (int) $now->format('Y');
        $currentMonth = (int) $now->format('n');
        $availableMonths = $charts->availableMonths($game->id);
        $availableYears = array_map('intval', array_keys($availableMonths));
        $availableYears[] = $currentYear;
        $availableYears = array_values(array_unique(array_filter($availableYears, fn(int $availableYear) => $availableYear >= 2000 && $availableYear <= $currentYear)));
        rsort($availableYears);
        $canonical = rtrim((string) app(AppSettingsService::class)->value('canonical_url', 'https://playonlinekhaiwal.com'), '/')
            . '/result/' . $game->slug;

        return response()
            ->view('frontend.market', compact('game', 'year', 'month', 'monthLabel', 'results', 'canonical', 'currentYear', 'currentMonth', 'availableYears'))
            ->header('X-Robots-Tag', $request->query->count() > 0 ? 'noindex, follow' : 'index, follow');
    }

    /**
     * AJAX endpoint for monthly results. It deliberately returns only the
     * requested active game's date/result pairs and never renders an indexable page.
     */
    public function chart(Request $request, MonthlyChartService $charts)
    {
        $data = $request->validate([
            'slug' => ['required', 'string', 'regex:/^[A-Za-z0-9-]+$/'],
            'year' => ['required', 'regex:/^[0-9]{4}$/'],
            'month' => ['required', 'integer', 'between:1,12'],
        ]);

        $game = Game::query()->where('status', 'active')->where('slug', $data['slug'])->firstOrFail();
        $now = now()->timezone(config('app.timezone'));
        $year = (int) $data['year'];
        $month = (int) $data['month'];
        abort_if($year < 2000 || $year > (int) $now->format('Y'), 404);
        abort_if($year === (int) $now->format('Y') && $month > (int) $now->format('n'), 404);

        [$monthStart, $monthEnd, $monthDays] = $charts->monthDates($year, $month);
        $results = $charts->gameRows($game, $monthStart, $monthEnd, $monthDays);

        return response()->json([
            'game' => ['name' => $game->name, 'slug' => $game->slug],
            'year' => $year,
            'month' => $month,
            'month_label' => $charts->monthLabel($year, $month),
            'days' => $results->map(fn(array $row) => [
                'day' => $row['date']->day,
                'result' => $row['number'],
                'status' => $row['status'],
            ])->values(),
        ])->header('Cache-Control', 'no-store');
    }

    /**
     * Render an active administrator-managed CMS page linked from the homepage navigation.
     */
    public function page(string $slug)
    {
        $page = Page::query()->where('slug', $slug)->where('status', 'active')->firstOrFail();

        return view('frontend.page', compact('page'));
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

    private function getFrontendResults(): array
    {
        $now = now()->timezone(config('app.timezone'));
        $games = Game::query()->where('status', 'active')->orderBy('serial')->orderBy('id')
            ->get(['id', 'name', 'slug', 'serial', 'result_time', 'last_result']);
        $businessDates = $this->businessDatesFor($games, $now);
        $resultDates = collect($businessDates)->flatMap(fn(array $dates) => array_values($dates))->unique()->values();
        $results = Result::query()->whereIn('game_id', $games->pluck('id'))->where('type', 'jodi')
            ->whereIn('game_date', $resultDates)->get(['game_id', 'game_date', 'number'])->groupBy('game_id');
        $latestPublishedAt = Result::query()
            ->whereIn('game_id', $games->pluck('id'))
            ->where('type', 'jodi')
            ->whereNotNull('game_date')
            ->whereDate('game_date', '<=', $now->toDateString())
            ->whereNotNull('number')
            ->where('number', '<>', '')
            ->selectRaw('game_id, MAX(COALESCE(updated_at, created_at)) as latest_published_at')
            ->groupBy('game_id')
            ->pluck('latest_published_at', 'game_id');

        return $games->map(function (Game $game) use ($results, $businessDates, $latestPublishedAt) {
            $gameResults = $results->get($game->id, collect());
            $dates = $businessDates[$game->id];
            $todayResult = optional($gameResults->firstWhere('game_date', $dates['today']))->number;
            $yesterdayResult = optional($gameResults->firstWhere('game_date', $dates['yesterday']))->number;

            return [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
                'serial' => (int) $game->serial,
                'latest_result_at' => $latestPublishedAt->get($game->id),
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
