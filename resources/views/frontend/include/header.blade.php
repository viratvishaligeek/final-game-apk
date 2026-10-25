<header class="bg-white border-bottom">
    <nav class="navbar navbar-expand-lg container py-3" aria-label="Main navigation">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-primary text-wrap" href="{{ route('index') }}" aria-label="{{ setting('title', 'Play Online Khaiwal') }} home">
            @if (filled(setting('site_logo')) && is_file(public_path('logos/' . basename(setting('site_logo')))))
                <img class="rounded-circle" src="{{ asset('logos/' . basename(setting('site_logo'))) }}" alt="{{ setting('title', 'Play Online Khaiwal') }}" loading="eager">
            @else
                <span class="rounded-circle bg-primary text-white p-2">POK</span>
            @endif
            <span>{{ setting('title', 'Play Online Khaiwal') }}</span>
        </a>
        <button class="navbar-toggler" type="button" data-menu-toggle data-bs-toggle="collapse" data-bs-target="#primary-nav" aria-controls="primary-nav" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="primary-nav">
            <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <a class="nav-link" href="{{ route('index') }}">Home</a>
                <a class="nav-link" href="{{ route('index') }}#today-results">Results</a>
                <a class="nav-link" href="{{ route('index') }}#market-charts">Monthly charts</a>
                <a class="nav-link" href="{{ route('community.index') }}">Guessing</a>
                <a class="nav-link" href="{{ route('index') }}#records">Records</a>
                <a class="nav-link" href="{{ route('information', ['page' => 'faq']) }}">Help / FAQ</a>
                @foreach ($navPages ?? collect() as $navPage)
                    <a class="nav-link" href="{{ route('information', ['page' => $navPage->slug]) }}">{{ $navPage->name }}</a>
                @endforeach
                <a class="btn btn-primary ms-lg-2" href="{{ route('frontend.app-download') }}">Download App ↓</a>
            </div>
        </div>
    </nav>
</header>
<div class="bg-primary text-white py-2">
    <div class="container d-flex flex-wrap align-items-center gap-2">
        <span class="fw-bold">📢</span><span class="small">
            @if (filled(setting('web_marquee')))
                {{ setting('web_marquee') }}
            @else
                Today's results · Previous results · Market schedules · Historical charts · Year-wise records
            @endif
        </span>
    </div>
</div>
