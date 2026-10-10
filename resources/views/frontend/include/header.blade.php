<header class="site-header">
    <div class="header-main wrap">
        <a class="brand" href="{{ route('index') }}" aria-label="{{ setting('title', 'Play Online Khaiwal') }} home">
            @if (filled(setting('site_logo')) && is_file(public_path('logos/' . basename(setting('site_logo')))))
                <img class="brand-emblem" src="{{ asset('logos/' . basename(setting('site_logo'))) }}" alt="{{ setting('title', 'Play Online Khaiwal') }}" loading="eager">
            @else
                <span class="brand-emblem">POK</span>
            @endif
            <span class="brand-word">{{ setting('title', 'Play Online Khaiwal') }}<small>{{ setting('site_tagline', 'SATTA KING RESULTS • MONTHLY CHARTS • HISTORICAL RECORDS') }}</small></span>
        </a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" data-menu-toggle><span></span><span></span><span></span><b class="sr-only">Toggle navigation</b></button>
        <nav class="primary-nav" id="primary-nav" aria-label="Main navigation">
            <a href="{{ route('index') }}">Home</a>
            <a href="{{ route('index') }}#today-results">Results</a>
            <a href="{{ route('index') }}#market-charts">Charts</a>
            <a href="{{ route('index') }}#records">Records</a>
            <a href="{{ route('information', ['page' => 'faq']) }}">Help / FAQ</a>
            @foreach (($navPages ?? collect()) as $navPage)
                <a href="{{ route('frontend.page', ['slug' => $navPage->slug]) }}">{{ $navPage->name }}</a>
            @endforeach
            <a class="nav-search" href="{{ route('frontend.app-download') }}" aria-label="Download App">↓ <span>Download App</span></a>
        </nav>
    </div>
</header>
<div class="ticker" aria-label="Quick navigation ticker">
    <span class="ticker-label">{{ setting('title', 'Play Online Khaiwal') }}</span>
    <div class="ticker-window"><div class="ticker-track">
        @if (filled(setting('ticker_text')))
            @for ($tickerCopy = 0; $tickerCopy < 2; $tickerCopy++)
                <span>{{ setting('ticker_text') }}</span><i>✦</i>
            @endfor
        @else
            <span>Today's results</span><i>✦</i><span>Previous results</span><i>✦</i><span>Market schedules</span><i>✦</i><span>Historical charts</span><i>✦</i><span>Year-wise records</span><i>✦</i>
            <span>Today's results</span><i>✦</i><span>Previous results</span><i>✦</i><span>Market schedules</span><i>✦</i><span>Historical charts</span><i>✦</i><span>Year-wise records</span>
        @endif
    </div></div>
</div>
