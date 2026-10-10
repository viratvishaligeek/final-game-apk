@extends('frontend.include.app')
@section('title', $title . ' | ' . setting('title', 'Play Online Khaiwal'))
@section('meta_description', $copy)
@section('content')
<div class="page-banner"><div class="wrap"><div class="breadcrumbs"><a href="{{ route('index') }}">Home</a><span>/</span><b>{{ $title }}</b></div><p class="eyebrow">SITE INFORMATION</p><h1>{{ $title }}</h1><p>Helpful information about published records and responsible use.</p></div></div>
<section class="section-block section-light"><div class="wrap information-panel"><p class="eyebrow">PLAY ONLINE KHAIWAL · INFORMATION DESK</p><h2>{{ $title }}</h2><p>{{ $copy }}</p>@if($page==='faq')
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
<a class="button button-gold" href="{{ route('index') }}">Back to results board ↗</a></div></section>
@endsection