@extends('frontend.include.app')
@section('title', $game->name . ' ' . $monthLabel . ' Satta King Result Chart | ' . setting('title', 'Play Online Khaiwal'))
@section('meta_description', 'Browse the ' . $monthLabel . ' Satta King result chart for ' . $game->name . ' on Play Online Khaiwal. Review saved date-wise records; unavailable results are marked pending.')
@section('meta_keywords', $game->name . ', ' . $monthLabel . ' result chart, Satta King monthly chart, Satta Result Chart')
@section('robots_content', $hasPublishedResults ? setting('robots_default', 'index,follow') : 'noindex,follow')
@section('structured_data')
    @php
        $chartCanonical = rtrim((string) setting('canonical_url', 'https://playonlinekhaiwal.com'), '/') . '/charts/' . $game->slug . '/' . $year . '/' . $month;
        $chartSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $game->name . ' ' . $monthLabel . ' Satta King Result Chart',
            'url' => $chartCanonical,
            'description' => 'Saved monthly result records for ' . $game->name . ' in ' . $monthLabel . '.',
            'breadcrumb' => [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim((string) setting('canonical_url', 'https://playonlinekhaiwal.com'), '/') . '/'],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Markets', 'item' => rtrim((string) setting('canonical_url', 'https://playonlinekhaiwal.com'), '/') . '/#markets'],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $game->name, 'item' => rtrim((string) setting('canonical_url', 'https://playonlinekhaiwal.com'), '/') . '/charts/' . $game->slug . '/' . $year . '/' . $month],
                    ['@type' => 'ListItem', 'position' => 4, 'name' => $monthLabel, 'item' => $chartCanonical],
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">@json($chartSchema, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>
@endsection
@section('content')
<section class="page-banner page-banner--blue">
    <div class="wrap">
        <div class="breadcrumbs">
            <a href="{{ route('index') }}">Home</a><span>/</span><a href="{{ route('index', ['year' => $year, 'month' => $month]) }}#markets">Markets</a><span>/</span><b>{{ $game->name }} · {{ $monthLabel }}</b>
        </div>
        <p class="eyebrow">MONTHLY RESULT CHART</p>
        <h1>{{ $game->name }} <span>{{ $monthLabel }}</span></h1>
        <p>Only saved records for this market and selected month are shown. Missing values are not predicted.</p>
    </div>
</section>
<section class="section-block section-light">
    <div class="wrap">
        <div class="archive-overview">
            <aside class="archive-feature">
                <span class="archive-symbol">▦</span>
                <p class="eyebrow">CHOOSE A PERIOD</p>
                <h2>{{ $game->name }}</h2>
                <form method="GET" action="{{ route('frontend.chart', ['slug' => $game->slug]) }}" class="monthly-chart-filter" data-month-filter>
                    <label for="game-chart-year">Year</label>
                    <select class="form-select" id="game-chart-year" name="year" data-chart-year>
                        @foreach ($availableYears as $yearOption)
                            <option value="{{ $yearOption }}" {{ (int) $yearOption === (int) $year ? 'selected' : '' }}>{{ $yearOption }}</option>
                        @endforeach
                    </select>
                    <label for="game-chart-month">Month</label>
                    <select class="form-select" id="game-chart-month" name="month" data-chart-month>
                        @foreach ($monthOptions as $monthOption)
                            <option value="{{ $monthOption }}" {{ (int) $monthOption === (int) $month ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromDate($year, $monthOption, 1, config('app.timezone'))->translatedFormat('F') }}</option>
                        @endforeach
                    </select>
                    <button class="button button-dark" type="submit">View month ↗</button>
                </form>
                <p class="small-note">Chart rows per page: {{ $chunkSize }}</p>
            </aside>
            <div class="archive-years">
                <div class="archive-years-head"><b>AVAILABLE MONTHS</b><span>{{ $game->name }}</span></div>
                @forelse ($availableYears as $yearOption)
                    <div class="archive-years-head"><b>{{ $yearOption }}</b></div>
                    <div class="year-links month-links">
                        @foreach (($availableMonths[$yearOption] ?? []) as $monthOption)
                            <a class="{{ (int) $yearOption === (int) $year && (int) $monthOption === (int) $month ? 'is-current' : '' }}" href="{{ route('frontend.chart', ['slug' => $game->slug, 'year' => $yearOption, 'month' => $monthOption]) }}">{{ \Carbon\Carbon::createFromDate($yearOption, $monthOption, 1, config('app.timezone'))->translatedFormat('F') }}</a>
                        @endforeach
                        @if ((int) $yearOption === (int) $year && !in_array((int) $month, $availableMonths[$yearOption] ?? [], true))
                            <a class="is-current" href="{{ route('frontend.chart', ['slug' => $game->slug, 'year' => $year, 'month' => $month]) }}">{{ $monthLabel }} · no results</a>
                        @endif
                    </div>
                @empty
                    <p class="archive-empty">No published result months are available for this market yet.</p>
                @endforelse
            </div>
        </div>

        <div class="page-toolbar">
            <h2>{{ $game->name }} · {{ $monthLabel }}</h2>
            <a class="button button-dark" href="{{ route('index', ['year' => $year, 'month' => $month]) }}#market-charts">All-market monthly chart ↗</a>
        </div>
        @if (!$hasPublishedResults)
            <div class="empty-state">
                <b>No published results for {{ $monthLabel }}.</b>
                <p>Select another available month above to browse this market's stored records.</p>
            </div>
        @else
            <div class="table-shell light-table"><div class="table-scroll">
                <table class="quick-record-table monthly-result-table">
                    <thead><tr><th>Game</th><th>Date</th><th>Result</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse ($results as $entry)
                        <tr>
                            <th scope="row">{{ $entry['game']->name }}</th>
                            <td>{{ $entry['date']->format('d M Y') }}</td>
                            <td><span class="table-result {{ $entry['status'] === 'published' ? 'is-published' : ($entry['status'] === 'upcoming' ? 'is-upcoming' : 'is-pending') }}">{{ $entry['number'] ?? '—' }}</span></td>
                            <td>{{ $entry['status'] === 'published' ? 'Published' : ($entry['status'] === 'upcoming' ? 'Not due yet' : 'Pending') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="table-empty">No result entries are available for this month.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div></div>
            <nav class="mt-3" aria-label="Monthly game chart pagination">{{ $results->links() }}</nav>
        @endif
    </div>
</section>
@endsection
@section('script')
<script>
document.querySelectorAll('[data-month-filter]').forEach(function (form) {
    const year = form.querySelector('[data-chart-year]');
    const month = form.querySelector('[data-chart-month]');
    if (year && month) {
        year.addEventListener('change', function () {
            month.value = '';
            form.submit();
        });
        month.addEventListener('change', function () {
            form.submit();
        });
    }
});
</script>
@endsection
