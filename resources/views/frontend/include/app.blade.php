<!doctype html>
<html lang="en">
<head>
    @include('frontend.include.meta')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontend/css/site-theme.css') }}">
    @yield('style')
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    @include('frontend.include.header')
    <main id="main-content">@yield('content')</main>
    @include('frontend.include.footer')
    <button class="back-top" type="button" aria-label="Back to top" data-back-top>↑</button>
    <script src="{{ asset('frontend/js/static-home.js') }}" defer></script>
    @yield('script')
</body>
</html>