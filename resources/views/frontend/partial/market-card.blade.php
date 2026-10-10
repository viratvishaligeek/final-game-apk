<article class="market-card market-card--{{ ($index ?? 0) % 6 }} {{ ($index ?? 0) === 0 ? 'market-card--featured' : '' }}">
    <div class="market-card-top"><span class="market-chip">MARKET {{ str_pad((string)(($index ?? 0)+1),2,'0',STR_PAD_LEFT) }}</span><span class="market-time">◷ {{ $game['time'] }}</span></div>
    <h3>{{ $game['name'] }}</h3>
    <div class="market-result-pair"><div><small>YESTERDAY</small><strong>{{ $game['yesterday'] }}</strong></div><span class="result-arrow">→</span><div class="today-value"><small>TODAY</small><strong>{{ $game['today'] }}</strong></div></div>
    <div class="market-card-foot"><span class="result-state {{ $game['today'] === '--' ? 'is-pending' : 'is-published' }}"><i></i>{{ $game['today'] === '--' ? 'Awaiting publication' : 'Published record' }}</span><a href="{{ route('frontend.market',['slug'=>$game['slug']]) }}">Open chart <span>↗</span></a></div>
</article>