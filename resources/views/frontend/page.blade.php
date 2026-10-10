@extends('frontend.include.app')
@section('title', $page->meta_title ?: $page->name . ' | ' . setting('title', 'Play Online Khaiwal'))
@section('meta_description', $page->meta_description ?: \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $page->content))) ?: setting('site_description', 'Play Online Khaiwal provides monthly result charts and historical records.'), 160))
@section('meta_keywords', $page->meta_keywords ?: setting('meta_keywords', ''))
@section('robots_content', $page->noindex ? 'noindex,follow' : setting('robots_default', 'index,follow'))
@section('structured_data')
    @php
        $pageCanonical = rtrim((string) setting('canonical_url', 'https://playonlinekhaiwal.com'), '/') . request()->getPathInfo();
        $pageDescription = $page->meta_description ?: \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $page->content))), 160);
        $pageSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $page->meta_title ?: $page->name,
            'url' => $pageCanonical,
            'description' => $pageDescription,
            'breadcrumb' => [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim((string) setting('canonical_url', 'https://playonlinekhaiwal.com'), '/') . '/'],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => $page->name, 'item' => $pageCanonical],
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">@json($pageSchema, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>
@endsection
@section('content')
    <section class="page-banner">
        <div class="wrap">
            <div class="breadcrumbs"><a href="{{ route('index') }}">Home</a><span>/</span><b>{{ $page->name }}</b></div>
            <p class="eyebrow">SITE INFORMATION</p>
            <h1>{{ $page->name }}</h1>
        </div>
    </section>
    <section class="section-block section-light">
        <div class="wrap">
            <article class="market-card">
                @if (session('notice'))
                    <div class="empty-state"><p>{{ session('notice') }}</p></div>
                @endif
                <div class="homepage-managed-content">{!! \App\Support\SafeHtml::sanitize((string) $page->content) !!}</div>
            </article>
        </div>
    </section>
@endsection
