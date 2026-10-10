<footer class="site-footer">
    <div class="footer-top wrap">
        <div class="footer-brand-block">
            <a class="brand" href="{{ route('index') }}">
                @if (filled(setting('site_logo')) && is_file(public_path('logos/' . basename(setting('site_logo')))))
                    <img class="brand-emblem" src="{{ asset('logos/' . basename(setting('site_logo'))) }}" alt="{{ setting('title', 'Satta 786') }}" loading="lazy">
                @else
                    <span class="brand-emblem">786</span>
                @endif
                <span class="brand-word">{{ setting('title', 'Satta 786') }}<small>{{ setting('site_tagline', 'RESULTS • RECORDS • CHARTS') }}</small></span>
            </a>
            <p>{{ setting('footer_description', setting('site_description', 'A structured reference for published market results, schedules, and historical records.')) }}</p>
            @if (filled(setting('email')))<p><a href="mailto:{{ setting('email') }}">{{ setting('email') }}</a></p>@endif
            @if (filled(setting('contact_phone')))<p><a href="tel:{{ preg_replace('/[^0-9+]/', '', setting('contact_phone')) }}">{{ setting('contact_phone') }}</a></p>@endif
            @if (filled(setting('contact_address')))<p>{{ setting('contact_address') }}</p>@endif
            <div class="footer-socials">
                @foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'telegram' => 'Telegram'] as $socialKey => $socialLabel)
                    @if (filled(setting('social_' . $socialKey)) && filter_var(setting('social_' . $socialKey), FILTER_VALIDATE_URL) && in_array(parse_url(setting('social_' . $socialKey), PHP_URL_SCHEME), ['http', 'https'], true))
                        <a href="{{ setting('social_' . $socialKey) }}" target="_blank" rel="noopener noreferrer">{{ $socialLabel }}</a>
                    @endif
                @endforeach
            </div>
        </div>
        <div><h3>Explore</h3><a href="{{ route('index') }}#today-results">Today's results</a><a href="{{ route('index') }}#markets">Market board</a><a href="{{ route('index') }}#records">Old records</a><a href="{{ route('index') }}#market-charts">Year-wise charts</a></div>
        <div><h3>Information</h3><a href="{{ route('information',['page'=>'about']) }}">About</a><a href="{{ route('information',['page'=>'faq']) }}">FAQ</a><a href="{{ route('information',['page'=>'contact']) }}">Contact</a><a href="{{ route('information',['page'=>'privacy-policy']) }}">Privacy policy</a>
            @foreach (($navPages ?? collect()) as $navPage)<a href="{{ route('frontend.page', ['slug' => $navPage->slug]) }}">{{ $navPage->name }}</a>@endforeach
        </div>
        <div><h3>Policies</h3><a href="{{ route('information',['page'=>'terms-and-conditions']) }}">Terms & conditions</a><a href="{{ route('information',['page'=>'disclaimer']) }}">Disclaimer</a><p class="footer-note">{{ setting('disclaimer_content', 'Information only. No prediction or financial outcome is guaranteed.') }}</p></div>
    </div>
    <div class="footer-bottom"><div class="wrap"><span>© {{ date('Y') }} {{ setting('copyright_text', setting('title', 'Satta 786')) }}</span><span>Use responsibly • Follow applicable local laws</span></div></div>
</footer>
@if (request()->routeIs('index'))
    @include('frontend.partial.homepage-sections', ['sections' => ($homepageSections ?? collect())->where('location', 'after_footer')])
@endif
