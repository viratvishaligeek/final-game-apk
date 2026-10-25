@extends('frontend.include.app')
@section('title',
    filled(setting('meta_title'))
    ? setting('meta_title')
    : 'Play Online Khaiwal | Satta King Results &
    Monthly Charts')
@section('robots_content', request()->hasAny(['year', 'month']) || (int) request()->query('page', 1) > 1 ?
    'noindex,follow' : setting('robots_default', 'index,follow'))
@section('meta_description',
    filled(setting('meta_description'))
    ? setting('meta_description')
    : (filled(setting('site_description'))
    ? setting('site_description')
    : 'Browse Satta King results, Satta Matka market
    records, and game-wise monthly charts on Play Online Khaiwal.'))
@section('content')
    <div class="bg-light border-bottom">
        <div class="container container d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
            <span class="small text-secondary">
                <b>DATE DESK</b>
                {{ now()->timezone(config('app.timezone'))->format('D, d M Y') }}</span>
            <span class="small text-secondary">
                <i>

                </i>
                {{ setting('announcement_text', "Records shown are read from the site's published result database") }}</span>
            <a href="{{ route('information', ['page' => 'disclaimer']) }}">legal notice ↗</a>
        </div>
    </div>

    <section class="bg-dark text-white py-5" id="home">
        <div class="row g-4 align-items-center container">
            <div class="col-lg-7">
                <p class="text-info small fw-bold mb-3">
                    <span >
                    </span> THE RESULT & RECORD CENTER
                </p>
                <h1>{{ setting('homepage_heading_line1', 'Every market.') }}<br>
                    <span>{{ setting('homepage_heading_line2', 'Every record.') }}</span>
                    <br>
                    <em>{{ setting('homepage_heading_line3', 'One clear board.') }}</em>
                </h1>
                <p class="lead text-white-50">
                    {{ setting('homepage_intro', 'Find published results, compare recent records, and move straight into the chart you need. Clear status labels separate available results from pending records.') }}
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn btn-warning" href="{{ route('frontend.app-download') }}">Download
                        App
                        <span>↓</span>
                    </a>
                </div>
                <div class="row g-3 mt-3">
                    <div>
                        <strong>{{ count($frontendResults) }}</strong>
                        <span>Active markets</span>
                    </div>
                    <div>
                        <strong>{{ count($recordYears) }}</strong>
                        <span>Record years</span>
                    </div>
                    <div>
                        <strong>{{ collect($frontendResults)->filter(fn($g) => $g['today'] !== '--')->count() }}</strong>
                        <span>Today's
                            published</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 card bg-primary text-white p-4">
                <div class="d-none d-none">

                </div>
                <div class="d-none d-none">

                </div>
                @php($featured = $featuredGame)
                @if ($featured)
                    <div class="h4 fw-bold">{{ $featured['name'] }}</div>
                    <div class="small text-white-50">{{ now()->timezone(config('app.timezone'))->format('d F Y') }}
                        <span>•</span> Scheduled {{ $featured['time'] }}
                    </div>
                    <div class="display-2 fw-bold text-center">{{ $featured['today'] }}</div>
                    <div class="small text-center">
                        {{ $featured['today'] === '--' ? 'NO RESULT PUBLISHED FOR CURRENT BUSINESS DATE' : 'Live Result' }}
                    </div>
                    <div class="d-flex flex-wrap justify-content-between gap-2 mt-3">
                        <span>कल आया था <b>{{ $featured['yesterday'] }}</b></span>
                        <a href="{{ route('frontend.market', ['slug' => $featured['slug']]) }}">View market history ↗</a>
                    </div>
                @else
                    <div class="h4 fw-bold">No active markets</div>
                    <p>Market records will appear when active markets are configured.</p>
                @endif
            </div>
        </div>
        <div class="d-none d-none">

        </div>
        <div class="d-none d-none">

        </div>
    </section>

    @include('frontend.partial.homepage-sections', [
        'sections' => $homepageSections->where('location', 'first_place'),
    ])

    <div class="container row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 py-4 container">
        <a href="#today-results">
            <span class="badge me-2 text-bg-danger">01</span>
            <span>
                <b>Today's
                    Result</b>
                <small>Published and pending</small>
            </span>
            <strong>↗</strong>
        </a>
        <a href="#markets">
            <span class="badge me-2 text-bg-info">02</span>
            <span>
                <b>Compare records</b>
                <small>Yesterday vs
                    today</small>
            </span>
            <strong>↗</strong>
        </a>
        <a href="#records">
            <span class="badge me-2 text-bg-warning">03</span>
            <span>
                <b>Old Result</b>
                <small>Years and
                    markets</small>
            </span>
            <strong>↗</strong>
        </a>
        <a href="#faq">
            <span class="badge me-2 text-bg-success">04</span>
            <span>
                <b>FAQ's</b>
                <small>Guide and
                    definitions</small>
            </span>
            <strong>↗</strong>
        </a>
    </div>

    <section class="py-5 bg-light" id="today-results">
        <div class="container">
            @include('frontend.partial.section-heading', [
                'eyebrow' => 'THE DAILY RESULT BOARD',
                'title' => 'Today’s <span>market results</span>',
                'description' =>
                    'Values are taken from stored records for each market’s current business date. A dash means no result is published for that date.',
            ])
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3">
                @forelse($remainingFrontendResults as $index => $game)
                    <article class="card h-100 p-3">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <span
                                class="badge text-bg-secondary">{{ str_pad((string) ($game['serial'] ?? $index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="badge rounded-pill {{ $game['today'] === '--' ? 'text-bg-warning' : 'text-bg-success' }}">
                                <i>
                                </i>{{ $game['today'] === '--' ? 'PENDING' : 'PUBLISHED' }}</span>
                        </div>
                        <h3>{{ $game['name'] }}</h3>
                        <div class="display-5 fw-bold text-center{{ $game['today'] === '--' ? 'display-5 fw-bold text-center--pending' : '' }}">
                            {{ $game['today'] }}
                        </div>
                        <div class="d-flex flex-wrap justify-content-between gap-2 small text-secondary">
                            <span>Scheduled {{ $game['time'] }}</span>
                            <span>Prev.
                                {{ $game['yesterday'] }}</span>
                        </div>
                        <a href="{{ route('frontend.market', ['slug' => $game['slug']]) }}">Full result history
                            <b>↗</b>
                        </a>
                </article>@empty<div class="alert alert-light">
                        <b>No active markets are configured.</b>
                        <p>Once a market is activated in the admin area, its published records will appear here.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    @include('frontend.partial.homepage-sections', [
        'sections' => $homepageSections->where('location', 'second_place'),
    ])
    @include('frontend.partial.homepage-sections', [
        'sections' => $homepageSections->where('location', 'second_place_another'),
    ])

    <section class="bg-primary text-white py-4">
        <div class="container container d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <p class="small text-uppercase fw-bold text-primary mb-2">NEED A QUICK REFERENCE?</p>
                <h2>Schedules, records and context.<br>
                    <span>Without the guesswork.</span>
                </h2>
                <p>Use the schedule displayed on each market card, then open its chart to browse stored dates. The page
                    never predicts missing results.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn btn-warning" href="#records">Go to record center ↗</a>
                <a class="btn btn btn-light" href="{{ route('information', ['page' => 'faq']) }}">Read the guide</a>
            </div>
            <div >RECORD<br>
                <b>DESK</b>
                <span>POK</span>
            </div>
        </div>
    </section>
    <section class="py-5 bg-light" id="markets">
        <div class="container">
            @include('frontend.partial.section-heading', [
                'eyebrow' => 'MARKET DIRECTORY',
                'title' => 'Choose your <span>market</span>',
                'description' =>
                    'Open an individual market page to browse its latest stored records and historical chart years.',
            ])
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <label class="form-control">
                    <span aria-hidden="true">⌕</span>
                    <input id="market-search" type="search" placeholder="Search market name…" aria-label="Search markets">
                    <kbd>/</kbd>
                </label>
                <span class="small text-secondary">{{ count($marketResults) }} markets available</span>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-3" id="market-grid">
                @forelse($marketResults as $index => $game)
                    @include('frontend.partial.market-card', ['game' => $game, 'index' => $index])@empty
                    <div class="alert alert-light">
                        <b>No markets to display</b>
                        <p>Activate a market from the admin area to show it here.</p>
                    </div>
                @endforelse
            </div>
            <p class="alert alert-light" id="no-markets" hidden>No market matches that search. Try another name.</p>
        </div>
    </section>

    @include('frontend.partial.homepage-sections', [
        'sections' => $homepageSections->where('location', 'third_place'),
    ])

    <section class="bg-primary text-white py-4">
        <div class="container container d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <p class="small text-uppercase fw-bold text-primary mb-2">NEED A QUICK REFERENCE?</p>
                <h2>Schedules, records and context.<br>
                    <span>Without the guesswork.</span>
                </h2>
                <p>Use the schedule displayed on each market card, then open its chart to browse stored dates. The page
                    never predicts missing results.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn btn-warning" href="#records">Go to record center ↗</a>
                <a class="btn btn btn-light" href="{{ route('information', ['page' => 'faq']) }}">Read the guide</a>
            </div>
            <div >RECORD<br>
                <b>DESK</b>
                <span>POK</span>
            </div>
        </div>
    </section>

    @include('frontend.partial.homepage-sections', [
        'sections' => $homepageSections->where('location', 'third_place_another'),
    ])

    <section class="py-5 bg-light" id="market-charts">
        <div class="container">
            @include('frontend.partial.section-heading', [
                'eyebrow' => 'CURRENT MONTH · ' . strtoupper($monthLabel),
                'title' => 'Monthly <span>market results</span>',
                'description' =>
                    'Saved results for each active market and calendar date in ' .
                    $monthLabel .
                    '. Missing values are shown as a dash.',
            ])
            @forelse ($monthlyTables as $tableIndex => $table)
                <div class="card p-3 bg-white card">
                    <div class="table-responsive" role="region" aria-label="Market results table {{ $tableIndex + 1 }}"
                        tabindex="0">
                        <table class="table table-striped table-hover align-middle table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">Date</th>
                                    @foreach ($table['games'] as $game)
                                        <th scope="col">
                                            <a
                                                href="{{ route('frontend.market', ['slug' => $game->slug]) }}">{{ $game->name }}</a>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($table['dates'] as $date)
                                    <tr>
                                        <th scope="row">{{ $date->format('d') }}</th>
                                        @foreach ($table['games'] as $game)
                                            <td>{{ $table['results'][$game->id][$date->day] ?? '—' }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="alert alert-light">
                    <b>No active markets are configured.</b>
                    <p>Activate a market in the admin panel to display its results.</p>
                </div>
            @endforelse
        </div>
    </section>

    @include('frontend.partial.homepage-sections', [
        'sections' => $homepageSections->where('location', 'before_footer'),
    ])

    <section class="py-5 bg-light" id="records">
        <div class="container">
            <div >
                <div class="card p-3">
                    <p class="small text-uppercase fw-bold text-primary mb-2">HISTORICAL REGISTER</p>
                    <h3>Old results.<br>
                        <span>Clear chronology.</span>
                    </h3>
                    <br>
                    @foreach ($games as $game)
                        <a class="btn btn btn-warning"
                            href="{{ route('frontend.market', ['slug' => $game->slug]) }}"> {{ $game->name }} Result
                            ↗</a>
                    @endforeach
                </div>
            </div>
    </section>

    <section class="py-5 bg-light" id="about">
        <div class="container">
            <div>@include('frontend.partial.section-heading', [
                'eyebrow' => 'RESULT READING GUIDE',
                'title' => 'Read the board <span>with clarity</span>',
                'description' => 'A quick guide to the labels and tools used across this result directory.',
            ])
                <div class="list-group">
                    <article>
                        <span>01</span>
                        <div>
                            <h3>Published result</h3>
                            <p>A number stored for that market and business date. The displayed value reflects the saved
                                record.</p>
                        </div>
                    </article>
                    <article>
                        <span>02</span>
                        <div>
                            <h3>Pending result</h3>
                            <p>A dash (—) means no result was found for the relevant date. It is not a prediction or an
                                estimated value.</p>
                        </div>
                    </article>
                    <article>
                        <span>03</span>
                        <div>
                            <h3>Historical chart</h3>
                            <p>Open a market chart to review saved records by date and use the year links to narrow the
                                archive.</p>
                        </div>
                    </article>
                    <article>
                        <span>04</span>
                        <div>
                            <h3>Corrected records</h3>
                            <p>If a record is corrected in the source system, the page reflects the database value on the
                                next request. This page does not independently verify a result.</p>
                        </div>
                    </article>
                </div>
            </div>
            <aside class="card p-3">
                <p class="small text-uppercase fw-bold text-primary mb-2">QUICK GLOSSARY</p>
                <h3>Terms you’ll see</h3>
                <dl>
                    <dt>Jodi</dt>
                    <dd>A two-digit result value recorded by the system.</dd>
                    <dt>Market time</dt>
                    <dd>The configured schedule for a market's result.</dd>
                    <dt>Business date</dt>
                    <dd>The date assigned to a result by that market’s date rules.</dd>
                    <dt>Archive year</dt>
                    <dd>A year discovered from saved result dates.</dd>
                </dl>
                <div class="alert alert-info">Historical patterns do not guarantee or reliably predict future results.</div>
            </aside>
        </div>
    </section>

    <section class="py-5 bg-dark text-white bg-dark text-white py-5" id="faq">
        <div class="container">
            @include('frontend.partial.section-heading', [
                'eyebrow' => 'HELP CENTER',
                'title' => 'Frequently asked <span>questions</span>',
                'description' =>
                    'Practical answers about records, status labels, and the information shown on this site.',
                'class' => 'heading-on-dark',
                'action' => ['url' => route('information', ['page' => 'faq']), 'label' => 'Full FAQ page'],
            ])
            <div class="row g-4">
                <div class="col-lg-4">
                    <span >?</span>
                    <h3>Need to find<br>a specific record?</h3>
                    <p>Start with the market board or use the year-wise chart finder to narrow your search.</p>
                    <a href="#markets" class="btn btn-outline-primary">Find a market ↗</a>
                </div>
                <div class="col-lg-8">
                    @forelse ($faqs as $faq)
                        <details class="accordion-item">
                            <summary>{{ $faq->question }}<span>+</span>
                            </summary>
                            <div class="accordion-body">{{ $faq->answer }}</div>
                        </details>
                    @empty
                        <p class="accordion-body">No frequently asked questions have been added yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="bg-light py-4" id="disclaimer">
        <div class="container container d-flex flex-wrap align-items-center gap-3">
            <div >!</div>
            <div>
                <p class="small text-uppercase fw-bold text-primary mb-2">IMPORTANT INFORMATION</p>
                <h2>Records are information — <span>not promises.</span>
                </h2>
                <p>{{ setting('disclaimer_content', 'This website presents stored result information for reference. It does not guarantee accuracy, predict future outcomes, or promise financial gains. Follow applicable local laws and make responsible decisions.') }}
                </p>
            </div>
            <a class="btn btn btn-dark" href="{{ route('information', ['page' => 'disclaimer']) }}">Full
                disclaimer
                ↗</a>
        </div>
    </section>
    <div class="container d-flex flex-wrap gap-3 py-3">
        <div class="container">
            <span>QUICK JUMP</span>
            <a href="#today-results">Today's results</a>
            <a href="#markets">Markets</a>
            <a href="#today-results">Comparison</a>
            <a href="#records">Archives</a>
            <a href="#market-charts">Charts</a>
            <a href="#faq">FAQs</a>
            <a href="#disclaimer">Disclaimer</a>
        </div>
    </div>
@endsection


@section('structured_data')
    <script
    type="application/ld+json">@json($homeSchema, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>
