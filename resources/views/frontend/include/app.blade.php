<!doctype html>
<html lang="en">
<head>
    @include('frontend.include.meta')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    @yield('style')
</head>
<body class="bg-light text-dark">
    <a class="visually-hidden-focusable" href="#main-content">Skip to content</a>
    @include('frontend.include.header')
    <main id="main-content">@yield('content')</main>
    @include('frontend.include.footer')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    @yield('script')
</body>
</html>
