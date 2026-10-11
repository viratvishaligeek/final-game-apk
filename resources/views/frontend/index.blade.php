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
        <div class="wrap freshness-inner"><span class="freshness-date"><b>DATE DESK</b>
                {{ now()->timezone(config('app.timezone'))->format('D, d M Y') }}</span><span class="freshness-note"><i></i>
                {{ setting('announcement_text', "Records shown are read from the site's published result database") }}</span><a
                href="{{ route('information', ['page' => 'disclaimer']) }}">legal notice ↗</a></div>
    </div>

    <section class="hero-shell" id="home">
        <div class="hero-grid wrap">
            <div class="hero-copy">
                <p class="hero-kicker"><span class="kicker-line"></span> THE RESULT & RECORD CENTER</p>
                <h1>{{ setting('homepage_heading_line1', 'Every market.') }}<br><span>{{ setting('homepage_heading_line2', 'Every record.') }}</span><br><em>{{ setting('homepage_heading_line3', 'One clear board.') }}</em>
                </h1>
                <p class="hero-intro">
                    {{ setting('homepage_intro', 'Find published results, compare recent records, and move straight into the chart you need. Clear status labels separate available results from pending records.') }}
                </p>
                <div class="hero-actions"><a class="button button-gold" href="{{ route('frontend.app-download') }}">Download
                        App
                        <span>↓</span></a><a class="button button-outline" href="{{ route('community.register') }}">Register
                        Now
                        <span>↗</span></a></div>
                <div class="hero-facts">
                    <div><strong>{{ count($frontendResults) }}</strong><span>Active markets</span></div>
                    <div><strong>{{ count($recordYears) }}</strong><span>Record years</span></div>
                    <div>
                        <strong>{{ collect($frontendResults)->filter(fn($g) => $g['today'] !== '--')->count() }}</strong><span>Today's
                            published</span>
                    </div>
                </div>
            </div>
            <div class="hero-spotlight">
                <div class="spotlight-orbit orbit-a"></div>
                <div class="spotlight-orbit orbit-b"></div>
                <div class="spotlight-top"><span class="spotlight-label">FEATURED MARKET</span><span
                        class="status-badge"><i></i> DATABASE STATUS</span></div>
                @php($featured = $featuredGame)
                @if ($featured)
                    <div class="spotlight-market">{{ $featured['name'] }}</div>
                    <div class="spotlight-date">{{ now()->timezone(config('app.timezone'))->format('d F Y') }}
                        <span>•</span> Scheduled {{ $featured['time'] }}
                    </div>
                    <div class="spotlight-number">{{ $featured['today'] }}</div>
                    <div class="spotlight-caption">
                        {{ $featured['today'] === '--' ? 'NO RESULT PUBLISHED FOR CURRENT BUSINESS DATE' : 'PUBLISHED RESULT FOR CURRENT BUSINESS DATE' }}
                    </div>
                    <div class="spotlight-bottom"><span>Previous record <b>{{ $featured['yesterday'] }}</b></span><a
                            href="{{ route('frontend.market', ['slug' => $featured['slug']]) }}">View market history ↗</a>
                    </div>
                @else
                    <div class="spotlight-market">No active markets</div>
                    <p>Market records will appear when active markets are configured.</p>
                @endif
            </div>
        </div>
        <div class="hero-decor hero-decor-one"></div>
        <div class="hero-decor hero-decor-two"></div>
    </section>

    @include('frontend.partial.homepage-sections', [
        'sections' => $homepageSections->where('location', 'first_place'),
    ])

    <div class="quick-paths wrap"><a href="#today-results"><span class="path-icon path-red">01</span><span><b>Today's
                    board</b><small>Published and pending</small></span><strong>↗</strong></a><a href="#today-results"><span
                class="path-icon path-cyan">02</span><span><b>Compare records</b><small>Yesterday vs
                    today</small></span><strong>↗</strong></a><a href="#records"><span
                class="path-icon path-gold">03</span><span><b>Archive vault</b><small>Years and
                    markets</small></span><strong>↗</strong></a><a href="#faq"><span
                class="path-icon path-green">04</span><span><b>How to read</b><small>Guide and
                    definitions</small></span><strong>↗</strong></a></div>

    <section class="section-block section-light" id="today-results">
        <div class="wrap">
            @include('frontend.partial.section-heading', [
                'eyebrow' => 'THE DAILY RESULT BOARD',
                'title' => 'Today’s <span>market results</span>',
                'description' =>
                    'Values are taken from stored records for each market’s current business date. A dash means no result is published for that date.',
            ])
            <div class="result-board-grid">
                @forelse($remainingFrontendResults as $index => $game)
                    <article class="result-board-card">
                        <div class="result-board-head">
                            <span
                                class="market-index">{{ str_pad((string) ($game['serial'] ?? $index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <span
                                class="result-state {{ $game['today'] === '--' ? 'is-pending' : 'is-published' }}"><i></i>{{ $game['today'] === '--' ? 'PENDING' : 'PUBLISHED' }}</span>
                        </div>
                        <h3>{{ $game['name'] }}</h3>
                        <div class="result-big {{ $game['today'] === '--' ? 'result-big--pending' : '' }}">
                            {{ $game['today'] }}
                        </div>
                        <div class="result-meta"><span>Scheduled {{ $game['time'] }}</span><span>Prev.
                                {{ $game['yesterday'] }}</span></div><a
                            href="{{ route('frontend.market', ['slug' => $game['slug']]) }}">Full result history
                            <b>↗</b></a>
                </article>@empty<div class="empty-state"><b>No active markets are configured.</b>
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

    <section class="info-ribbon">
        <div class="wrap ribbon-inner">
            <div>
                <p class="eyebrow">NEED A QUICK REFERENCE?</p>
                <h2>Schedules, records and context.<br><span>Without the guesswork.</span></h2>
                <p>Use the schedule displayed on each market card, then open its chart to browse stored dates. The page
                    never predicts missing results.</p>
            </div>
            <div class="ribbon-actions"><a class="button button-gold" href="#records">Go to record center ↗</a><a
                    class="button button-light" href="{{ route('information', ['page' => 'faq']) }}">Read the guide</a>
            </div>
            <div class="ribbon-stamp">RECORD<br><b>DESK</b><span>POK</span></div>
        </div>
    </section>
    <section class="section-block section-light" id="markets">
        <div class="wrap">
            @include('frontend.partial.section-heading', [
                'eyebrow' => 'MARKET DIRECTORY',
                'title' => 'Choose your <span>market</span>',
                'description' =>
                    'Open an individual market page to browse its latest stored records and historical chart years.',
            ])
            <div class="market-tools"><label class="market-search"><span aria-hidden="true">⌕</span><input
                        id="market-search" type="search" placeholder="Search market name…"
                        aria-label="Search markets"><kbd>/</kbd></label><span
                    class="market-count">{{ count($marketResults) }} markets available</span></div>
            <div class="market-grid" id="market-grid">
                @forelse($marketResults as $index => $game)
                    @include('frontend.partial.market-card', ['game' => $game, 'index' => $index])@empty
                    <div class="empty-state"><b>No markets to display</b>
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

    <section class="info-ribbon">
        <div class="wrap ribbon-inner">
            <div>
                <p class="eyebrow">NEED A QUICK REFERENCE?</p>
                <h2>Schedules, records and context.<br><span>Without the guesswork.</span></h2>
                <p>Use the schedule displayed on each market card, then open its chart to browse stored dates. The page
                    never predicts missing results.</p>
            </div>
            <div class="ribbon-actions"><a class="button button-gold" href="#records">Go to record center ↗</a><a
                    class="button button-light" href="{{ route('information', ['page' => 'faq']) }}">Read the guide</a>
            </div>
            <div class="ribbon-stamp">RECORD<br><b>DESK</b><span>POK</span></div>
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
                                    <th scope="col">Date</th>
                                    @foreach ($table['games'] as $game)
                                        <th scope="col"><a
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
                <div class="empty-state"><b>No active markets are configured.</b>
                    <p>Activate a market in the admin panel to display its results.</p>
                </div>
            @endforelse
        </div>
    </section>

    @include('frontend.partial.homepage-sections', [
        'sections' => $homepageSections->where('location', 'before_footer'),
    ])

    <section class="section-block section-light" id="records">
        <div class="wrap">
            <div class="arch">
                <div class="archive-feature">
                    <p class="eyebrow">HISTORICAL REGISTER</p>
                    <h3>Old results.<br><span>Clear chronology.</span></h3>
                    <br>
                    @foreach ($games as $game)
                        <a class="button button-gold" style="padding: 10px; margin: 5px"
                            href="{{ route('frontend.market', ['slug' => $game->slug]) }}"> {{ $game->name }} Result ↗</a>
                    @endforeach
                </div>
            </div>
    </section>

    <section class="section-block section-light" id="about">
        <div class="wrap learn-grid">
            <div>@include('frontend.partial.section-heading', [
                'eyebrow' => 'RESULT READING GUIDE',
                'title' => 'Read the board <span>with clarity</span>',
                'description' => 'A quick guide to the labels and tools used across this result directory.',
            ])<div class="guide-list">
                    <article><span>01</span>
                        <div>
                            <h3>Published result</h3>
                            <p>A number stored for that market and business date. The displayed value reflects the saved
                                record.</p>
                        </div>
                    </article>
                    <article><span>02</span>
                        <div>
                            <h3>Pending result</h3>
                            <p>A dash (—) means no result was found for the relevant date. It is not a prediction or an
                                estimated value.</p>
                        </div>
                    </article>
                    <article><span>03</span>
                        <div>
                            <h3>Historical chart</h3>
                            <p>Open a market chart to review saved records by date and use the year links to narrow the
                                archive.</p>
                        </div>
                    </article>
                    <article><span>04</span>
                        <div>
                            <h3>Corrected records</h3>
                            <p>If a record is corrected in the source system, the page reflects the database value on the
                                next request. This page does not independently verify a result.</p>
                        </div>
                    </article>
                </div>
            </div>
            <aside class="glossary-panel">
                <p class="eyebrow">QUICK GLOSSARY</p>
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
                <div class="glossary-note">Historical patterns do not guarantee or reliably predict future results.</div>
            </aside>
        </div>
    </section>

    <section class="section-block section-dark faq-section" id="faq">
        <div class="wrap">
            @include('frontend.partial.section-heading', [
                'eyebrow' => 'HELP CENTER',
                'title' => 'Frequently asked <span>questions</span>',
                'description' =>
                    'Practical answers about records, status labels, and the information shown on this site.',
                'class' => 'heading-on-dark',
                'action' => ['url' => route('information', ['page' => 'faq']), 'label' => 'Full FAQ page'],
            ])
            <div class="faq-layout">
                <div class="faq-lead"><span class="faq-mark">?</span>
                    <h3>Need to find<br>a specific record?</h3>
                    <p>Start with the market board or use the year-wise chart finder to narrow your search.</p><a
                        href="#markets" class="text-action">Find a market ↗</a>
                </div>
                <div class="faq-list">
                    @forelse ($faqs as $faq)
                        <details class="faq-item">
                            <summary>{{ $faq->question }}<span>+</span></summary>
                            <div class="faq-answer">{{ $faq->answer }}</div>
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
                <p class="eyebrow">IMPORTANT INFORMATION</p>
                <h2>Records are information — <span>not promises.</span></h2>
                <p>{{ setting('disclaimer_content', 'This website presents stored result information for reference. It does not guarantee accuracy, predict future outcomes, or promise financial gains. Follow applicable local laws and make responsible decisions.') }}
                </p>
            </div><a class="button button-dark" href="{{ route('information', ['page' => 'disclaimer']) }}">Full
                disclaimer
                ↗</a>
        </div>
    </section>
    <div class="jump-bar">
        <div class="wrap"><span>QUICK JUMP</span><a href="#today-results">Today's results</a><a
                href="#markets">Markets</a><a href="#today-results">Comparison</a><a href="#records">Archives</a><a
                href="#market-charts">Charts</a><a href="#faq">FAQs</a><a href="#disclaimer">Disclaimer</a></div>
    </div>
@endsection


@section('structured_data')
    <script type="application/ld+json">@json($homeSchema, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>
