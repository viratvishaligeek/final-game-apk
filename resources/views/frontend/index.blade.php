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
    <div class="freshness-bar">
        <div class="wrap freshness-inner">
            <span class="freshness-date">
                <b>DATE DESK</b>
                {{ now()->timezone(config('app.timezone'))->format('D, d M Y') }}</span>
            <span class="freshness-note">
                <i>

                </i>
                {{ setting('announcement_text', "Records shown are read from the site's published result database") }}</span>
            <a href="{{ route('page', ['page' => 'disclaimer']) }}">legal notice ↗</a>
        </div>
    </div>

    <section class="hero-shell" id="home">
        <div class="hero-grid wrap">
            <div class="hero-copy">
                <p class="hero-kicker">
                    <span class="kicker-line">
                    </span> THE RESULT & RECORD CENTER
                </p>
                <h1>{{ setting('homepage_heading_line1', 'Every market.') }}<br>
                    <span>{{ setting('homepage_heading_line2', 'Every record.') }}</span>
                    <br>
                    <em>{{ setting('homepage_heading_line3', 'One clear board.') }}</em>
                </h1>
                <p class="hero-intro">
                    {{ setting('homepage_intro', 'Find published results, compare recent records, and move straight into the chart you need. Clear status labels separate available results from pending records.') }}
                </p>
                <div class="hero-actions">
                    <a class="button button-gold" href="{{ setting('app_download_url') }}" target="_blank">Download
                        App
                        <span>↓</span>
                    </a>
                </div>
                <div class="hero-facts">
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
            <div class="hero-spotlight">
                <div class="spotlight-orbit orbit-a">

                </div>
                <div class="spotlight-orbit orbit-b">

                </div>
                @php($featured = $featuredGame)
                @if ($featured)
                    <div class="spotlight-market">{{ $featured['name'] }}</div>
                    <div class="spotlight-date">🗓️ {{ now()->timezone(config('app.timezone'))->format('d F Y') }} |
                        <span>🕓</span> {{ $featured['time'] }}
                    </div>
                    <div class="spotlight-caption">
                        {{ $featured['today'] === '--' ? 'NO RESULT PUBLISHED FOR CURRENT BUSINESS DATE' : '🔴 LIVE RESULT' }}
                    </div>
                    <div class="spotlight-number">{{ $featured['today'] }}</div>
                    <div class="spotlight-bottom">
                        <span>🕓 कल आया था <b>{{ $featured['yesterday'] }}</b></span>
                        <a href="{{ route('frontend.market', ['slug' => $featured['slug']]) }}">View market history ↗</a>
                    </div>
                @else
                    <div class="spotlight-market">No active markets</div>
                    <p>Market records will appear when active markets are configured.</p>
                @endif
            </div>
        </div>
        <div class="hero-decor hero-decor-one">

        </div>
        <div class="hero-decor hero-decor-two">

        </div>
    </section>

    @include('frontend.partial.homepage-sections', [
        'sections' => $homepageSections->where('location', 'first_place'),
    ])

    <div class="quick-paths wrap">
        <a href="#today-results">
            <span class="path-icon path-red">01</span>
            <span>
                <b>Today's
                    Result</b>
                <small>Published and pending</small>
            </span>
            <strong>↗</strong>
        </a>
        <a href="#markets">
            <span class="path-icon path-cyan">02</span>
            <span>
                <b>Compare records</b>
                <small>Yesterday vs
                    today</small>
            </span>
            <strong>↗</strong>
        </a>
        <a href="#records">
            <span class="path-icon path-gold">03</span>
            <span>
                <b>Old Result</b>
                <small>Years and
                    markets</small>
            </span>
            <strong>↗</strong>
        </a>
        <a href="#faq">
            <span class="path-icon path-green">04</span>
            <span>
                <b>FAQ's</b>
                <small>Guide and
                    definitions</small>
            </span>
            <strong>↗</strong>
        </a>
    </div>

    <section class="section-block section-light" id="today-results">
        <div class="wrap">
            @include('frontend.partial.section-heading', [
                'eyebrow' => 'THE DAILY RESULT BOARD',
                'title' => 'Today’s <span>market results</span>',
            ])
            <div class="result-board-grid">
                @forelse($remainingFrontendResults as $index => $game)
                    <article class="result-board-card">
                        <h3>{{ $game['name'] }}</h3>
                        <span>( {{ $game['time'] }} )</span>
                        <hr class="m-1">
                        <span>
                            आज का रिजल्ट
                        </span>
                        <div class="result-big {{ $game['today'] === '--' ? 'result-big--pending' : '' }}">
                            {{ $game['today'] }}
                        </div>
                        <a href="{{ route('frontend.market', ['slug' => $game['slug']]) }}">Full result history
                            <b>↗</b>
                        </a>
                </article>@empty<div class="empty-state">
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
    <section class="section-block section-light" id="markets">
        <div class="wrap">
            @include('frontend.partial.section-heading', [
                'eyebrow' => 'MARKET DIRECTORY',
                'title' => 'Choose your <span>market</span>',
                'description' =>
                    'Check today`s and yesterday`s Matka results with market-wise numbers and previous records.',
            ])
            <div class="market-tools">
                <label class="market-search">
                    <span aria-hidden="true">⌕</span>
                    <input id="market-search" type="search" placeholder="Search market name…" aria-label="Search markets">
                    <kbd>/</kbd>
                </label>
                <span class="market-count">{{ count($marketResults) }} markets available</span>
            </div>
            <div class="market-grid" id="market-grid">
                @forelse($marketResults as $index => $game)
                    @include('frontend.partial.market-card', ['game' => $game, 'index' => $index])@empty
                    <div class="empty-state">
                        <b>No markets to display</b>
                        <p>Activate a market from the admin area to show it here.</p>
                    </div>
                @endforelse
            </div>
            <p class="empty-state" id="no-markets" hidden>No market matches that search. Try another name.</p>
        </div>
    </section>

    @include('frontend.partial.homepage-sections', [
        'sections' => $homepageSections->where('location', 'third_place'),
    ])

    <section class="info-ribbon py-2" style="border: 2px solid goldenrod">
        <div class="wrap ribbon-inner">
            <div>
                <p class="eyebrow">MARKET DIRECTORY</p>
                <h2>Today's & Yesterday's Satta Results<br>
                </h2>
                <p>Explore today's and yesterday's market-wise Satta results on Play Online Khaiwal. Check published
                    results, compare previous-day records and browse historical charts for each market.</p>
            </div>
        </div>
    </section>

    @include('frontend.partial.homepage-sections', [
        'sections' => $homepageSections->where('location', 'third_place_another'),
    ])

    <section class="section-block section-light" id="market-charts">
        <div class="wrap">
            @include('frontend.partial.section-heading', [
                'eyebrow' => 'CURRENT MONTH · ' . strtoupper($monthLabel),
                'title' => 'Monthly <span>market results</span>',
                'description' =>
                    'Saved results for each active market and calendar date in ' .
                    $monthLabel .
                    '. Missing values are shown as a dash.',
            ])
            @forelse ($monthlyTables as $tableIndex => $table)
                <div class="table-shell light-table homepage-results-shell">
                    <div class="table-scroll" role="region" aria-label="Market results table {{ $tableIndex + 1 }}"
                        tabindex="0">
                        <table class="quick-record-table homepage-results-table">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-white">Date</th>
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
                <div class="empty-state">
                    <b>No active markets are configured.</b>
                    <p>Activate a market in the admin panel to display its results.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="section-block p-0 section-light" id="records">
        <div class="wrap">
            <div class="arch">
                <div class="archive-feature">
                    <p class="eyebrow">HISTORICAL REGISTER</p>
                    <h3>Old Monthly Results.</h3>
                    <br>
                    @foreach ($games as $game)
                        <a class="btn btn-sm button-gold  m-1"
                            href="{{ route('frontend.market', ['slug' => $game->slug]) }}"> {{ $game->name }}
                            ↗</a>
                    @endforeach
                </div>
            </div>
    </section>

    @include('frontend.partial.homepage-sections', [
        'sections' => $homepageSections->where('location', 'before_footer'),
    ])
    <br>
    <section class="section-block section-dark faq-section" id="faq">
        <div class="wrap">
            @include('frontend.partial.section-heading', [
                'eyebrow' => 'HELP CENTER',
                'title' => 'Frequently asked <span>questions</span>',
                'description' =>
                    'Practical answers about records, status labels, and the information shown on this site.',
                'class' => 'heading-on-dark',
            ])
            <div class="faq-layout">
                <div class="faq-list">
                    @forelse ($faqs as $faq)
                        <details class="faq-item">
                            <summary>{{ $faq->question }}<span>+</span>
                            </summary>
                            <div class="faq-answer bg-warning fs-6 text-dark">
                                <span class=" text-danger">
                                    Ans:
                                </span>
                                {{ $faq->answer }}
                            </div>
                        </details>
                    @empty
                        <p class="faq-answer">No frequently asked questions have been added yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="disclaimer-section" id="disclaimer">
        <div class="wrap disclaimer-inner">
            <div class="disclaimer-icon">!</div>
            <div>
                <h2>Disclaimer & Records are information — <span>not promises.</span>
                </h2>
                <p>{{ Str::limit(setting('disclaimer_content'), 100, '...') }}
                </p>
            </div>
            <a class="button button-dark" href="{{ route('page', ['page' => 'disclaimer']) }}">Full
                disclaimer
                ↗</a>
        </div>
    </section>

    <div class="jump-bar">
        <div class="wrap">
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
