@extends('frontend.include.app')
@section('title', $title . ' | ' . setting('title', 'Play Online Khaiwal'))
@section('meta_description', \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($copy))), 160))
@if ($page === 'faq' && $faqs->isNotEmpty())
    @section('structured_data')
        @php
            $faqSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faqs
                    ->map(
                        fn($faq) => [
                            '@type' => 'Question',
                            'name' => strip_tags((string) $faq->question),
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text' => strip_tags((string) $faq->answer),
                            ],
                        ],
                    )
                    ->values()
                    ->all(),
            ];
        @endphp
        <script type="application/ld+json">@json($faqSchema, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>
    @endsection
@endif
@section('content')
    <div class="bg-primary text-white py-5">
        <div class="container">
            <div class="small mb-3"><a href="{{ route('index') }}">Home</a><span>/</span><b>{{ $title }}</b></div>
            <p class="small text-uppercase fw-bold text-primary mb-2">SITE INFORMATION</p>
            <h1>{{ $title }}</h1>
            <p>Helpful information about published records and responsible use.</p>
        </div>
    </div>
    <section class="py-5 bg-light">
        <div class="container card p-4">
            <p class="small text-uppercase fw-bold text-primary mb-2">PLAY ONLINE KHAIWAL · INFORMATION DESK</p>
            <h2>{{ $title }}</h2>
            @if ($page === 'disclaimer')
                <div class="mb-3">{!! $copy !!}</div>
            @else
                <p>{{ $copy }}</p>
            @endif

            @if (session('notice'))
                <div class="alert alert-light">
                    <p>{{ session('notice') }}</p>
                </div>
            @endif

            @if ($page === 'faq')
                <div class="col-lg-8">
                    @forelse ($faqs as $faq)
                        <details class="accordion-item">
                            <summary>{{ $faq->question }}<span>+</span></summary>
                            <div class="accordion-body">{{ $faq->answer }}</div>
                        </details>
                    @empty
                        <p>No frequently asked questions have been added yet.</p>
                    @endforelse
                </div>
            @endif

            <a class="btn btn btn-warning" href="{{ route('index') }}">Back to results board ↗</a>
        </div>
    </section>
@endsection
