@extends('frontend.include.app')
@section('title', $title . ' | ' . setting('title', 'Play Online Khaiwal'))
@section('meta_description', \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags(\App\Support\SafeHtml::sanitize((string) $copy)))), 160))
@if ($page === 'faq' && $faqs->isNotEmpty())
    @section('structured_data')
        @php
            $faqSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faqs->map(fn ($faq) => [
                    '@type' => 'Question',
                    'name' => strip_tags((string) $faq->question),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => strip_tags((string) $faq->answer),
                    ],
                ])->values()->all(),
            ];
        @endphp
        <script type="application/ld+json">@json($faqSchema, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>
    @endsection
@endif
@section('content')
<div class="page-banner">
    <div class="wrap">
        <div class="breadcrumbs"><a href="{{ route('index') }}">Home</a><span>/</span><b>{{ $title }}</b></div>
        <p class="eyebrow">SITE INFORMATION</p>
        <h1>{{ $title }}</h1>
        <p>Helpful information about published records and responsible use.</p>
    </div>
</div>
<section class="section-block section-light">
    <div class="wrap information-panel">
        <p class="eyebrow">PLAY ONLINE KHAIWAL · INFORMATION DESK</p>
        <h2>{{ $title }}</h2>
        @if ($page === 'disclaimer')
            <div class="homepage-managed-content">{!! \App\Support\SafeHtml::sanitize((string) $copy) !!}</div>
        @else
            <p>{{ $copy }}</p>
        @endif

        @if (session('notice'))
            <div class="empty-state"><p>{{ session('notice') }}</p></div>
        @endif

        @if ($page === 'faq')
            <div class="faq-list faq-list-light">
                @forelse ($faqs as $faq)
                    <details class="faq-item">
                        <summary>{{ $faq->question }}<span>+</span></summary>
                        <div class="faq-answer">{{ $faq->answer }}</div>
                    </details>
                @empty
                    <p>No frequently asked questions have been added yet.</p>
                @endforelse
            </div>
        @endif

        <a class="button button-gold" href="{{ route('index') }}">Back to results board ↗</a>
    </div>
</section>
@endsection
