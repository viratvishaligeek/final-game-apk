<!doctype html>
<html lang="en">

<head>
    @include('frontend.include.meta')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="{{ asset('frontend/css/site-theme.css') }}">
    @yield('style')
</head>

<body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    @include('frontend.include.header')
    <main id="main-content">@yield('content')</main>
    @include('frontend.include.footer')
    <button class="back-top" type="button" aria-label="Back to top" data-back-top>↑</button>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
    <script src="{{ asset('frontend/js/static-home.js') }}" defer></script>
    @yield('script')
</body>

</html>
