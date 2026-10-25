<article class="market-card p-2 market-card--{{ ($index ?? 0) % 6 }}">
    <div class="market-card-top">
        <h3 class="my-2">{{ $game['name'] }}</h3>
        <span class="market-time">◷ {{ $game['time'] }}</span>
    </div>
    <div class="market-result-pair text-center border-info">
        <div>
            <p class="mb-0 text-danger fs-4">{ कल }</p>
            <strong>{{ $game['yesterday'] }}</strong>
        </div>
        <span class="badge bg-success">LIVE</span>
        <div class="today-value">
            <p class="mb-0 text-success fs-4">{ आज }</p>
            <strong>{{ $game['today'] }}</strong>
        </div>
    </div>
    <div class="market-card-foot">
        <a href="{{ route('frontend.market', ['slug' => $game['slug']]) }}">Open chart <span>↗</span>
        </a>
    </div>
</article>
