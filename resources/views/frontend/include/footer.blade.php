<footer class="bg-dark text-white mt-5">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-12 col-md-6 col-lg-5">
                <a class="d-inline-flex align-items-center gap-2 text-white text-decoration-none fw-bold mb-3" href="{{ route('index') }}">
                    @if (filled(setting('site_logo')) && is_file(public_path('logos/' . basename(setting('site_logo')))))
                        <img class="rounded-circle" src="{{ asset('logos/' . basename(setting('site_logo'))) }}" alt="{{ setting('title', 'Play Online Khaiwal') }}" loading="lazy">
                    @else
                        <span class="rounded-circle bg-primary text-white p-2">POK</span>
                    @endif
                    {{ setting('title', 'Play Online Khaiwal') }}
                </a>
                <p class="text-white-50">{{ setting('footer_description', setting('site_description', 'A structured reference for published market results, schedules, and historical records.')) }}</p>
                @if (filled(setting('email')))<p><a class="link-light" href="mailto:{{ setting('email') }}">{{ setting('email') }}</a></p>@endif
                @if (filled(setting('contact_phone')))<p><a class="link-light" href="tel:{{ preg_replace('/[^0-9+]/', '', setting('contact_phone')) }}">{{ setting('contact_phone') }}</a></p>@endif
                @if (filled(setting('contact_address')))<p>{{ setting('contact_address') }}</p>@endif
                <div class="d-flex flex-wrap gap-2">
                    @foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'telegram' => 'Telegram', 'whatsapp' => 'WhatsApp'] as $socialKey => $socialLabel)
                        @if (filled(setting('social_' . $socialKey)) && filter_var(setting('social_' . $socialKey), FILTER_VALIDATE_URL) && in_array(parse_url(setting('social_' . $socialKey), PHP_URL_SCHEME), ['http', 'https'], true))
                            <a class="btn btn-sm btn-outline-light" href="{{ setting('social_' . $socialKey) }}" target="_blank" rel="noopener noreferrer">{{ $socialLabel }}</a>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-3">
                <h2 class="h5">Explore</h2>
                <ul class="list-unstyled d-grid gap-2">
                    <li><a class="link-light" href="{{ route('index') }}#today-results">Today's results</a></li>
                    <li><a class="link-light" href="{{ route('index') }}#markets">Market board</a></li>
                    <li><a class="link-light" href="{{ route('index') }}#records">Old Records</a></li>
                    <li><a class="link-light" href="{{ route('index') }}#market-charts">Monthly charts</a></li>
                    <li><a class="link-light" href="{{ route('information', ['page' => 'terms-and-conditions']) }}">Terms &amp; conditions</a></li>
                    <li><a class="link-light" href="{{ route('information', ['page' => 'disclaimer']) }}">Disclaimer</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-3 col-lg-4">
                <h2 class="h5">Information</h2>
                <ul class="list-unstyled d-grid gap-2">
                    <li><a class="link-light" href="{{ route('information', ['page' => 'about']) }}">About</a></li>
                    <li><a class="link-light" href="{{ route('information', ['page' => 'faq']) }}">FAQ</a></li>
                    <li><a class="link-light" href="{{ route('information', ['page' => 'contact']) }}">Contact</a></li>
                    <li><a class="link-light" href="{{ route('information', ['page' => 'privacy-policy']) }}">Privacy policy</a></li>
                    @foreach ($navPages ?? collect() as $navPage)
                        <li><a class="link-light" href="{{ route('information', ['page' => $navPage->slug]) }}">{{ $navPage->name }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    <div class="border-top border-secondary py-3">
        <div class="container d-flex flex-wrap justify-content-between gap-2 small">
            <span>{{ setting('copyright_text', '© ' . date('Y') . ' ' . setting('title', 'Play Online Khaiwal')) }}</span>
            <span>Use responsibly • Follow applicable local laws</span>
        </div>
    </div>
</footer>
@if (request()->routeIs('index'))
    @include('frontend.partial.homepage-sections', ['sections' => ($homepageSections ?? collect())->where('location', 'after_footer')])
@endif
