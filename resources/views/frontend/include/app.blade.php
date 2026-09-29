<!DOCTYPE html>
<html lang="hi" class="scroll-smooth">

<head>
    @include('frontend.include.meta')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Noto+Sans+Devanagari:wght@400;700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('frontend') }}/css/site-theme.css">
    <link rel="stylesheet" href="{{ URL::asset('frontend') }}/css/custom-animations.css">
    @yield('style')
</head>

<body class="bg-white text-black font-sans overflow-x-hidden">
    @include('frontend.include.header')
    <section id="freshness" class="freshness-wrap">
        <div class="freshness-headline">Daily Gali Disawar result of 29 September 2026 for Delhi Bazar, Shri Ganesh,
            Faridabad, Ghaziabad, Gali and Disawar with complete old record chart 2014–2026.</div>
        <div class="freshness-disclaimer">
            <b>DISCLAIMER:</b> This website is an independent informational platform. We do not promote gambling. Users
            are responsible for following local laws.
            <a class="freshness-more" href="disclaimer.php">Read more…</a>
        </div>
        <div class="freshness-updated">Updated: September 29, 2026, 19:50:49 IST</div>
    </section>
    @yield('content')
    @include('frontend.partial.sticky-button')
    @include('frontend.include.footer')
    @yield('script')
</body>

</html>
