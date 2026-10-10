@extends('frontend.include.app')
@section('content')
@php
$markets = [
 ['name'=>'Delhi Bazar','time'=>'03:00 PM','tone'=>'blue'],
 ['name'=>'Shri Ganesh','time'=>'04:30 PM','tone'=>'violet'],
 ['name'=>'Faridabad','time'=>'05:50 PM','tone'=>'amber'],
 ['name'=>'Ghaziabad','time'=>'09:20 PM','tone'=>'teal'],
 ['name'=>'Gali','time'=>'11:20 PM','tone'=>'rose'],
 ['name'=>'Disawar','time'=>'04:00 AM','tone'=>'indigo'],
];
@endphp
<main class="static-site">
<section class="hero" id="home">
 <div class="hero-copy">
  <p class="eyebrow"><span class="eyebrow-dot"></span> RESULT INFORMATION · STATIC PREVIEW</p>
  <h1>Results at a glance.<br><span>Records made simple.</span></h1>
  <p class="hero-description">A cleaner way to browse market schedules and historical record layouts. This preview uses demonstration content and is not connected to a live result service.</p>
  <div class="hero-actions"><a class="button button-primary" href="#today-results">Explore result board <span>↘</span></a><a class="button button-secondary" href="#record-chart">Browse record guide</a></div>
  <div class="hero-proof"><span class="proof-icon">✓</span><span>Clear labels</span><i></i><span>Mobile-friendly</span><i></i><span>No live data connection</span></div>
 </div>
 <div class="hero-board" aria-label="Illustrative result board">
  <div class="board-top"><div><span class="board-kicker">PREVIEW BOARD</span><h2>Market overview</h2></div><span class="preview-pill"><span></span> DEMO</span></div>
  <div class="board-date"><span class="calendar-icon">▦</span><span><b>Daily overview</b><small>Illustrative values · not live</small></span><span class="board-arrow">↗</span></div>
  <div class="board-feature"><div><span class="feature-label">FEATURED MARKET</span><h3>Delhi Bazar</h3><p>Sample schedule · 03:00 PM</p></div><div class="feature-number">—<small>DEMO</small></div></div>
  <div class="board-mini-grid"><div><span>GALI</span><b>--</b><small>Placeholder</small></div><div><span>DISAWAR</span><b>--</b><small>Placeholder</small></div></div>
  <div class="board-foot"><span class="status-dot"></span> Static sample · backend not connected</div>
 </div><div class="hero-orb hero-orb-one"></div><div class="hero-orb hero-orb-two"></div>
</section>
<section class="trust-strip" aria-label="Preview features"><div><span class="strip-icon">▦</span><span><b>Organized records</b><small>Scan schedules in one place</small></span></div><div><span class="strip-icon">⌕</span><span><b>Quick market search</b><small>Filter this demo board locally</small></span></div><div><span class="strip-icon">◷</span><span><b>Responsive layout</b><small>Designed for phone and desktop</small></span></div></section>
<section class="content-section" id="today-results">
 <div class="section-heading"><div><p class="eyebrow">THE RESULT BOARD</p><h2>Market schedule <span>& overview</span></h2><p>Static sample interface. Result values are intentionally blank.</p></div><span class="section-count">06 MARKETS</span></div>
 <div class="board-toolbar"><label class="search-box"><span>⌕</span><input id="market-search" type="search" placeholder="Search markets..." aria-label="Search markets"><kbd>/</kbd></label><div class="filter-group" role="group" aria-label="Filter markets"><button class="filter-button is-active" type="button" data-filter="all" aria-pressed="true">All markets</button><button class="filter-button" type="button" data-filter="morning" aria-pressed="false">Morning</button><button class="filter-button" type="button" data-filter="evening" aria-pressed="false">Evening</button></div></div>
 <div class="market-grid" id="market-grid">
 @foreach ($markets as $market)
 <article class="market-card market-{{ $market['tone'] }}" data-market="{{ strtolower($market['name']) }}" data-period="{{ $market['name'] === 'Disawar' ? 'morning' : 'evening' }}">
  <div class="market-card-top"><span class="market-symbol" aria-hidden="true">{{ strtoupper(substr($market['name'],0,1)) }}</span><span class="market-time">{{ $market['time'] }}</span></div><h3>{{ $market['name'] }}</h3><p class="market-subtitle">Sample schedule</p>
  <div class="market-result-row"><span>Today <b>--</b></span><span>Previous <b>--</b></span></div>
  <div class="market-card-foot"><span class="demo-tag">DEMO DATA</span><a href="#record-chart" aria-label="View record guide for {{ $market['name'] }}">Record guide <span>↗</span></a></div>
 </article>
 @endforeach
 <p class="no-results" id="no-markets" hidden>No markets match your search. Try another name.</p>
 </div>
 <p class="data-note"><span>ⓘ</span> These names and times are static design examples, not confirmation of current schedules. No database or API is queried.</p>
</section>
<section class="records-section" id="record-chart">
 <div class="records-copy"><p class="eyebrow">BROWSE WITH CLARITY</p><h2>A better way to<br><span>read records.</span></h2><p>This is a preview of how historical records and date-based navigation can look later. Backend services are intentionally disconnected.</p><a href="#record-table" class="text-link">View sample record layout <span>→</span></a></div>
 <div class="record-panel"><div class="record-panel-head"><div><span class="panel-icon">▤</span><span><b>Record overview</b><small>Static table preview</small></span></div><span class="demo-label">DEMO</span></div><div class="record-stats"><div><small>Markets</small><b>06</b></div><div><small>Result entries</small><b>--</b></div><div><small>Data source</small><b>Static</b></div></div><div class="record-bars" aria-hidden="true"><span style="height:36%"></span><span style="height:54%"></span><span style="height:42%"></span><span style="height:74%"></span><span style="height:58%"></span><span style="height:88%"></span><span style="height:64%"></span><span style="height:46%"></span><span style="height:70%"></span><span style="height:52%"></span><span style="height:82%"></span><span style="height:60%"></span></div><p class="chart-caption">Decorative illustration only — not actual results.</p></div>
</section>
<section class="content-section sample-table-section" id="record-table"><div class="section-heading"><div><p class="eyebrow">SAMPLE RECORD TABLE</p><h2>Market records</h2><p>A responsive table pattern ready for future integration.</p></div></div>
<div class="table-wrap"><table class="record-table"><thead><tr><th scope="col">Market</th><th scope="col">Scheduled time</th><th scope="col">Previous</th><th scope="col">Today</th><th scope="col">Status</th></tr></thead><tbody>
@foreach ($markets as $market)<tr><th scope="row">{{ $market['name'] }}</th><td>{{ $market['time'] }}</td><td><span class="empty-value">--</span></td><td><span class="empty-value">--</span></td><td><span class="status-chip">Static preview</span></td></tr>@endforeach
</tbody></table></div><p class="table-caption">Placeholder values are not published results.</p>
</section>
<section class="info-grid" id="about"><article class="info-card"><span class="info-icon">✳</span><h3>About this preview</h3><p>A static interface for browsing sample schedules and record layouts. Backend services remain disconnected.</p></article><article class="info-card"><span class="info-icon">⌁</span><h3>Responsible use</h3><p>This is an informational UI demonstration. It does not offer betting, deposits, withdrawals, or gambling transactions.</p></article><article class="info-card"><span class="info-icon">◈</span><h3>Future integration</h3><p>Approved data sources can later replace sample fields without rebuilding the overall UI.</p></article></section>
<section class="faq-section" id="faq"><div class="section-heading"><div><p class="eyebrow">HELP & CLARITY</p><h2>Frequently asked questions</h2></div></div><details class="faq-item"><summary>Are these live or official results?</summary><p>No. This is a static UI preview. All result fields are placeholders and no live service or database is connected.</p></details><details class="faq-item"><summary>Can I search the markets?</summary><p>Yes. Search and time filters work locally in your browser and do not make a server request.</p></details><details class="faq-item"><summary>Can backend integration be added later?</summary><p>Yes. The interface is organized into reusable sections for future approved backend data.</p></details></section>
</main>
@endsection
