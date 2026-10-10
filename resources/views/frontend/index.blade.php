@extends('frontend.include.app')
@section('title', setting('meta_title', setting('title', 'Satta 786 Results Today, Market Board & Historical Charts')))
@section('meta_description', setting('meta_description', setting('site_description', 'Browse published market results, compare today and yesterday, and open market-wise historical charts and year-wise records.')))
@section('content')
    <div class="freshness-bar">
        <div class="wrap freshness-inner"><span class="freshness-date"><b>DATE DESK</b>
                {{ now()->timezone(config('app.timezone'))->format('D, d M Y') }}</span><span class="freshness-note"><i></i>
                Records shown are read from the site's published result database</span><a
                href="{{ route('information', ['page' => 'disclaimer']) }}">Data & legal notice ↗</a></div>
    </div>

    @include('frontend.partial.homepage-sections', ['sections' => $homepageSections->where('location', 'first_place')])

    <section class="hero-shell" id="home">
        <div class="hero-grid wrap">
            <div class="hero-copy">
                <p class="hero-kicker"><span class="kicker-line"></span> THE RESULT & RECORD CENTER</p>
                <h1>Every market.<br><span>Every record.</span><br><em>One clear board.</em></h1>
                <p class="hero-intro">Find published results, compare recent records, and move straight into the chart you
                    need. Clear status labels separate available results from pending records.</p>
                <div class="hero-actions"><a class="button button-gold" href="#today-results">Download App
                        <span>↓</span></a><a class="button button-outline" href="#records">Register Now
                        <span>↗</span></a></div>
                <div class="hero-facts">
                    <div><strong>{{ count($marketResults) }}</strong><span>Active markets</span></div>
                    <div><strong>{{ count($recordYears) }}</strong><span>Record years</span></div>
                    <div>
                        <strong>{{ collect($frontendResults)->filter(fn($g) => $g['today'] !== '--')->count() }}</strong><span>Today's
                            published</span></div>
                </div>
            </div>
            <div class="hero-spotlight">
                <div class="spotlight-orbit orbit-a"></div>
                <div class="spotlight-orbit orbit-b"></div>
                <div class="spotlight-top"><span class="spotlight-label">FEATURED MARKET</span><span
                        class="status-badge"><i></i> DATABASE STATUS</span></div>
                @php($featured = $frontendResults[0] ?? null)
                @if ($featured)
                    <div class="spotlight-market">{{ $featured['name'] }}</div>
                    <div class="spotlight-date">{{ now()->timezone(config('app.timezone'))->format('d F Y') }}
                        <span>•</span> Scheduled {{ $featured['time'] }}</div>
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

    @include('frontend.partial.homepage-sections', ['sections' => $homepageSections->where('location', 'first_place_another')])

    <div class="quick-paths wrap"><a href="#today-results"><span class="path-icon path-red">01</span><span><b>Today's
                    board</b><small>Published and pending</small></span><strong>↗</strong></a><a href="#quick-record"><span
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
                @forelse($frontendResults as $index => $game)
                    <article class="result-board-card {{ $index === 0 ? 'result-board-card--lead' : '' }}">
                        <div class="result-board-head"><span
                                class="market-index">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span><span
                                class="result-state {{ $game['today'] === '--' ? 'is-pending' : 'is-published' }}"><i></i>{{ $game['today'] === '--' ? 'PENDING' : 'PUBLISHED' }}</span>
                        </div>
                        <h3>{{ $game['name'] }}</h3>
                        <div class="result-big {{ $game['today'] === '--' ? 'result-big--pending' : '' }}">{{ $game['today'] }}
                        </div>
                        <div class="result-meta"><span>Scheduled {{ $game['time'] }}</span><span>Prev.
                                {{ $game['yesterday'] }}</span></div><a
                            href="{{ route('frontend.market', ['slug' => $game['slug']]) }}">Full result history <b>↗</b></a>
                </article>@empty<div class="empty-state"><b>No active markets are configured.</b>
                        <p>Once a market is activated in the admin area, its published records will appear here.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    @include('frontend.partial.homepage-sections', ['sections' => $homepageSections->where('location', 'second_place')])

   <section class="info-ribbon">
        <div class="wrap ribbon-inner">
            <div>
                <p class="eyebrow">NEED A QUICK REFERENCE?</p>
                <h2>Schedules, records and context.<br><span>Without the guesswork.</span></h2>
                <p>Use the schedule displayed on each market card, then open its chart to browse stored dates. The page
                    never predicts missing results.</p>
            </div>
            <div class="ribbon-actions"><a class="button button-gold" href="#records">Go to record center ↗</a><a
                    class="button button-light" href="{{ route('information', ['page' => 'faq']) }}">Read the guide</a></div>
            <div class="ribbon-stamp">RECORD<br><b>DESK</b><span>786</span></div>
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

    @include('frontend.partial.homepage-sections', ['sections' => $homepageSections->where('location', 'third_place')])

    <section class="info-ribbon">
        <div class="wrap ribbon-inner">
            <div>
                <p class="eyebrow">NEED A QUICK REFERENCE?</p>
                <h2>Schedules, records and context.<br><span>Without the guesswork.</span></h2>
                <p>Use the schedule displayed on each market card, then open its chart to browse stored dates. The page
                    never predicts missing results.</p>
            </div>
            <div class="ribbon-actions"><a class="button button-gold" href="#records">Go to record center ↗</a><a
                    class="button button-light" href="{{ route('information', ['page' => 'faq']) }}">Read the guide</a></div>
            <div class="ribbon-stamp">RECORD<br><b>DESK</b><span>786</span></div>
        </div>
    </section>

    @include('frontend.partial.homepage-sections', ['sections' => $homepageSections->where('location', 'third_place_another')])

    <section class="section-block section-light" id="market-charts">
        <div class="wrap">
            @include('frontend.partial.section-heading', [
                'eyebrow' => 'CURRENT-MONTH RESULT CHART',
                'title' => 'Monthly <span>market chart</span>',
                'description' => 'Published values are shown from saved records. A dash marks a date with no stored result; future dates are not due yet.',
            ])
            <div class="page-toolbar"><h2>{{ $monthLabel }}</h2><span>{{ $monthlyGames->total() }} active markets · {{ $chunkSize }} markets per page</span></div>
            <div class="table-shell light-table"><div class="table-scroll">
                <table class="quick-record-table monthly-result-table">
                    <thead><tr><th>Market</th>@foreach ($monthDays as $day)<th>{{ $day->format('d') }}</th>@endforeach</tr></thead>
                    <tbody>
                    @forelse ($monthlyGames as $monthlyGame)
                        <tr>
                            <th scope="row"><a href="{{ route('frontend.market', ['slug' => $monthlyGame->slug]) }}">{{ $monthlyGame->name }}</a></th>
                            @foreach ($monthDays as $day)
                                @php($dayResult = $monthlyGame->results->firstWhere('game_date', $day->toDateString()))
                                <td><span class="table-result {{ $dayResult ? 'is-published' : 'is-pending' }}" title="{{ $dayResult ? 'Published result' : ($day->gt(now()->timezone(config('app.timezone'))->startOfDay()) ? 'Not due yet' : 'No result published') }}">{{ $dayResult && $dayResult->number !== '' ? (string) $dayResult->number : '—' }}</span></td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($monthDays) + 1 }}" class="table-empty">No active markets are configured.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div></div>
            <nav class="mt-3" aria-label="Monthly result chart pagination">{{ $monthlyGames->links() }}</nav>
        </div>
    </section>

    @include('frontend.partial.homepage-sections', ['sections' => $homepageSections->where('location', 'before_footer')])

    <section class="section-block section-light" id="records">
        <div class="wrap">
            @include('frontend.partial.section-heading', [
                'eyebrow' => 'HISTORICAL RECORD CENTER',
                'title' => 'The archive <span>vault</span>',
                'description' =>
                    'Browse supported years and open market-specific records. Archive years are discovered from saved result dates.',
            ])
            <div class="archive-overview">
                <div class="archive-feature"><span class="archive-symbol">▦</span>
                    <p class="eyebrow">HISTORICAL REGISTER</p>
                    <h3>Old results.<br><span>Clear chronology.</span></h3>
                    <p>Choose a market and a supported year to review saved jodi records in date order.</p><a
                        class="button button-gold" href="#market-charts">Explore year-wise charts ↗</a>
                </div>
                <div class="archive-years">
                    <div class="archive-years-head"><b>AVAILABLE YEARS</b><span>{{ count($recordYears) }}
                            year{{ count($recordYears) === 1 ? '' : 's' }}</span></div>
                    @forelse($recordYears as $year)
                        <a href="#market-charts" class="year-tile"><span>{{ $year }}</span><small>Browse market
                            charts</small><b>↗</b></a>@empty<div class="archive-empty">No historical result years are
                            available yet.</div>
                    @endforelse
                </div>
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
                <p>{{ setting('disclaimer_content', 'This website presents stored result information for reference. It does not guarantee accuracy, predict future outcomes, or promise financial gains. Follow applicable local laws and make responsible decisions.') }}</p>
            </div><a class="button button-dark" href="{{ route('information', ['page' => 'disclaimer']) }}">Full disclaimer
                ↗</a>
        </div>
    </section>
    @include('frontend.partial.homepage-sections', ['sections' => $homepageSections->where('location', 'after_footer')])

    <div class="jump-bar">
        <div class="wrap"><span>QUICK JUMP</span><a href="#today-results">Today's results</a><a
                href="#markets">Markets</a><a href="#quick-record">Comparison</a><a href="#records">Archives</a><a
                href="#market-charts">Charts</a><a href="#faq">FAQs</a><a href="#disclaimer">Disclaimer</a></div>
    </div>
@endsection
