@extends('frontend.include.app')
@section('title', $title . ' | ' . setting('title', 'Play Online Khaiwal'))
@section('meta_description', \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($meta_description))), 160))
@section('structured_data')
    @php
        $pageCanonical =
            rtrim((string) setting('canonical_url', 'https://playonlinekhaiwal.com'), '/') . request()->getPathInfo();
        $pageDescription =
            $page->meta_description ?:
            \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $page->content))), 160);
        $pageSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $page->meta_title ?: $page->name,
            'url' => $pageCanonical,
            'description' => $pageDescription,
            'breadcrumb' => [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Home',
                        'item' => rtrim((string) setting('canonical_url', 'https://playonlinekhaiwal.com'), '/') . '/',
                    ],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => $page->name, 'item' => $pageCanonical],
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">@json($pageSchema, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>
@endsection
@section('content')
    <section class="section-block section-light">
        <div class="wrap information-panel">
            <h2>{{ $page->name }}</h2>
            <p>{!! $page->content !!}</p>
            <a class="button button-gold" href="{{ route('index') }}">Back to results board ↗</a>
        </div>
    </section>
@endsection
