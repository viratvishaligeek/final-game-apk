@php
    $defaultTitle = setting('meta_title', setting('title', 'Satta 786 Results & Historical Charts'));
    $defaultDescription = setting('meta_description', setting('site_description', 'Browse published market results, schedules, and historical result charts by market and year.'));
    $siteName = setting('title', 'Satta 786');
    $metaImage = setting('og_image', setting('site_logo'));
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', $defaultTitle)</title>
<meta name="description" content="@yield('meta_description', $defaultDescription)">
@if (filled(setting('meta_keywords')) || filled(trim($__env->yieldContent('meta_keywords'))))
    <meta name="keywords" content="@yield('meta_keywords', setting('meta_keywords', ''))">
@endif
<link rel="canonical" href="{{ url()->current() }}">
@if (filled(setting('site_favicon')))
    <link rel="icon" href="{{ asset('logos/' . basename(setting('site_favicon'))) }}">
@endif
<meta name="theme-color" content="#071329">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="@yield('title', $defaultTitle)">
<meta property="og:description" content="@yield('meta_description', $defaultDescription)">
<meta property="og:url" content="{{ url()->current() }}">
@if ($metaImage)
    <meta property="og:image" content="{{ asset('logos/' . basename($metaImage)) }}">
@endif
<meta name="robots" content="@yield('robots_content', 'index,follow')">
