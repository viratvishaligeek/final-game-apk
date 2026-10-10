@extends('frontend.include.app')
@section('title', 'Page not found | Satta 786')
@section('meta_description', 'The requested page could not be found. Return to the result board or choose a market.')
@section('content')
<section class="page-banner"><div class="wrap"><p class="eyebrow">ERROR 404 · RECORD NOT FOUND</p><h1>This page <span>isn't here.</span></h1><p>The link may be old or the market/year may not have a published archive.</p></div></section>
<section class="section-block section-light"><div class="wrap information-panel"><h2>Back to the result desk</h2><p>Return to the homepage to choose an active market or browse the available record years.</p><a class="button button-gold" href="{{ route('index') }}">Return home ↗</a></div></section>
@endsection