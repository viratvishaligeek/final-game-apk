<!doctype html>
<html lang="en">
<head>
    @include('frontend.include.meta')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontend/css/static-home.css') }}">
    @yield('style')
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ url('/') }}" aria-label="Satta 786 home"><span class="brand-mark">S</span><span class="brand-name">satta<span>786</span><small>RESULT DIRECTORY</small></span></a>
            <button class="menu-toggle" id="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-nav"><span></span><span></span><span></span><b class="sr-only">Toggle navigation</b></button>
            <nav class="primary-nav" id="primary-nav" aria-label="Main navigation">
                <a class="nav-link active" href="#home">Home</a><a class="nav-link" href="#today-results">Market board</a><a class="nav-link" href="#record-chart">Records</a><a class="nav-link" href="#about">About</a><a class="nav-button" href="#faq">How it works <span aria-hidden="true">↗</span></a>
            </nav>
        </div>
    </header>
    <div class="preview-banner"><span class="banner-dot"></span><strong>STATIC PREVIEW</strong><span>Demo content only · backend and live results are disabled</span></div>
    <div id="main-content">@yield('content')</div>
    <footer class="site-footer">
        <div class="footer-main"><a class="brand footer-brand" href="{{ url('/') }}"><span class="brand-mark">S</span><span class="brand-name">satta<span>786</span><small>RESULT DIRECTORY</small></span></a><p>A simple, responsive result-information interface. This preview is not a live result service.</p><nav aria-label="Footer navigation"><a href="#about">About</a><a href="#record-chart">Records</a><a href="#faq">FAQs</a></nav></div>
        <div class="footer-bottom"><span>© {{ date('Y') }} Satta 786 · Frontend preview</span><span>Sample data only · Not financial or gambling advice</span></div>
    </footer>
    <script src="{{ asset('frontend/js/static-home.js') }}" defer></script>
    @yield('script')
</body>
</html>
