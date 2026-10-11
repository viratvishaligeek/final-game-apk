@extends('frontend.include.app')
@php
    $currentYear = (int) now()->timezone(config('app.timezone'))->format('Y');
    $currentMonth = (int) now()->timezone(config('app.timezone'))->format('n');
    $currentMonthLabel = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1, config('app.timezone'))->translatedFormat('F Y');
@endphp
@section('title', $game->name . ' ' . $currentMonthLabel . ' Satta King Result Chart | ' . setting('title', 'Play Online Khaiwal'))
@section('meta_description', 'Open the ' . $currentMonthLabel . ' monthly result chart for ' . $game->name . ' on Play Online Khaiwal.')
@section('content')
<section class="page-banner">
    <div class="wrap">
        <div class="breadcrumbs"><a href="{{ route('index') }}">Home</a><span>/</span><a href="{{ route('index') }}#markets">Markets</a><span>/</span><b>{{ $game->name }}</b></div>
        <p class="eyebrow">MONTHLY RESULT CHART</p>
        <h1>{{ $game->name }} <span>{{ $currentMonthLabel }}</span></h1>
        <p>Game records are now browsed one calendar month at a time.</p>
    </div>
</section>
<section class="section-block section-light">
    <div class="wrap">
        <div class="empty-state">
            <b>Choose a month to view this market's chart.</b>
            <p>Historical records remain available through the year and month selector.</p>
            <a class="button button-dark" href="{{ route('frontend.chart', ['slug' => $game->slug, 'year' => $currentYear, 'month' => $currentMonth]) }}">Open {{ $currentMonthLabel }} chart ↗</a>
        </div>
    </div>
</section>
@endsection
