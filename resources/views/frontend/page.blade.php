@extends('frontend.include.app')
@section('title', $page->meta_title ?: $page->name)
@section('meta_description', $page->meta_description ?: (setting('meta_description', setting('site_description', 'Browse published market results and historical records.'))))
@section('meta_keywords', $page->meta_keywords ?: setting('meta_keywords', ''))
@section('robots_content', $page->noindex ? 'noindex,follow' : 'index,follow')
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
                <div class="homepage-managed-content">{!! nl2br(e(strip_tags((string) $page->content))) !!}</div>
            </article>
        </div>
    </section>
@endsection
