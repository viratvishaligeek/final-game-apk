@php
    $canonicalBase = rtrim((string) setting('canonical_url', 'https://playonlinekhaiwal.com'), '/');
    $defaultTitle = setting('meta_title', setting('title', 'Play Online Khaiwal | Satta King Results & Monthly Charts'));
    $defaultDescription = setting('meta_description', setting('site_description', 'Browse Satta King results, Satta Matka market records, and game-wise monthly charts on Play Online Khaiwal.'));
    $siteName = setting('title', 'Play Online Khaiwal');
    $sectionTitle = trim($__env->yieldContent('title', $defaultTitle));
    $sectionDescription = trim($__env->yieldContent('meta_description', $defaultDescription));
    $useGlobalSocialDefaults = request()->routeIs('index') && !request()->filled('year') && !request()->filled('month');
    $ogTitle = $useGlobalSocialDefaults ? setting('og_title', $sectionTitle) : $sectionTitle;
    $ogDescription = $useGlobalSocialDefaults ? setting('og_description', $sectionDescription) : $sectionDescription;
    $metaImage = setting('og_image', setting('site_logo'));
    $canonicalPath = request()->getPathInfo();
    $canonicalUrl = $canonicalBase . ($canonicalPath === '/' ? '' : $canonicalPath);
    if (request()->routeIs('index') && isset($selectedYear, $selectedMonth) && (request()->filled('year') || request()->filled('month'))) {
        $canonicalUrl .= '?' . http_build_query(['year' => $selectedYear, 'month' => $selectedMonth]);
    }
    $safeSocialLinks = collect([
        setting('social_facebook'),
        setting('social_instagram'),
        setting('social_youtube'),
        setting('social_telegram'),
        setting('social_whatsapp'),
    ])->filter(fn ($url) => is_string($url) && filter_var($url, FILTER_VALIDATE_URL) && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true))->values()->all();
    $organizationSchema = [
        '@type' => 'Organization',
        'name' => $siteName,
        'url' => $canonicalBase . '/',
        'description' => setting('site_description', $defaultDescription),
        'sameAs' => $safeSocialLinks,
    ];
    if ($metaImage && is_file(public_path('logos/' . basename($metaImage)))) {
        $organizationSchema['logo'] = $canonicalBase . '/logos/' . basename($metaImage);
    }
    $siteSchema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            $organizationSchema,
            [
                '@type' => 'WebSite',
                'name' => $siteName,
                'url' => $canonicalBase . '/',
                'inLanguage' => 'en',
            ],
        ],
    ];
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', $defaultTitle)</title>
<meta name="description" content="@yield('meta_description', $defaultDescription)">
@if (filled(setting('meta_keywords')) || filled(trim($__env->yieldContent('meta_keywords'))))
    <meta name="keywords" content="@yield('meta_keywords', setting('meta_keywords', ''))">
@endif
<link rel="canonical" href="{{ $canonicalUrl }}">
@if (filled(setting('site_favicon')) && is_file(public_path('logos/' . basename(setting('site_favicon')))))
    <link rel="icon" href="{{ $canonicalBase . '/logos/' . basename(setting('site_favicon')) }}">
@endif
<meta name="theme-color" content="#071329">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
@if ($metaImage && is_file(public_path('logos/' . basename($metaImage))))
    <meta property="og:image" content="{{ $canonicalBase . '/logos/' . basename($metaImage) }}">
@endif
<meta name="twitter:card" content="{{ setting('twitter_card', 'summary_large_image') }}">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDescription }}">
@if ($metaImage && is_file(public_path('logos/' . basename($metaImage))))
    <meta name="twitter:image" content="{{ $canonicalBase . '/logos/' . basename($metaImage) }}">
@endif
<meta name="robots" content="@yield('robots_content', setting('robots_default', 'index,follow'))">
<script type="application/ld+json">@json($siteSchema, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>
@yield('structured_data')
