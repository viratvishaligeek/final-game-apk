@if (request()->routeIs('index'))
    @include('frontend.partial.homepage-sections', [
        'sections' => ($homepageSections ?? collect())->where('location', 'after_footer'),
    ])
@endif
<footer class="site-footer bg-dark text-light pt-5">
    <div class="container">
        <div class="row g-4 g-lg-5 pb-4">

            <div class="col-lg-5 col-md-6">
                <a class="d-inline-flex align-items-center gap-3 text-decoration-none text-white mb-3"
                    href="{{ route('index') }}">

                    @if (filled(setting('site_logo')) && is_file(public_path('logos/' . basename(setting('site_logo')))))
                        <img src="{{ asset('logos/' . basename(setting('site_logo'))) }}" alt="{{ setting('title') }}"
                            width="55" height="55" class="rounded-3 object-fit-contain" loading="lazy">
                    @endif

                    <span class="fs-4 fw-bold">
                        {{ setting('title') }}
                        <small class="d-block text-secondary fs-6 fw-normal">
                            {{ setting('site_tagline') }}
                        </small>
                    </span>
                </a>

                @if (filled(setting('footer_description')))
                    <p class="text-white-50 lh-lg mb-4 footer-description">
                        {{ setting('footer_description') }}
                    </p>
                @endif

                <div class="d-flex flex-column gap-3">

                    @if (filled(setting('email')))
                        <a href="mailto:{{ setting('email') }}" class="footer-contact-link">
                            <span class="footer-icon">✉</span>
                            <span>{{ setting('email') }}</span>
                        </a>
                    @endif

                    @if (filled(setting('contact_phone')))
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', setting('contact_phone')) }}"
                            class="footer-contact-link">
                            <span class="footer-icon">☎</span>
                            <span>{{ setting('contact_phone') }}</span>
                        </a>
                    @endif

                    @if (filled(setting('contact_address')))
                        <div class="footer-contact-link">
                            <span class="footer-icon">⌖</span>
                            <span>{{ setting('contact_address') }}</span>
                        </div>
                    @endif

                </div>

                <div class="mt-4">
                    <h6 class="fw-semibold mb-3">Follow Us</h6>

                    <div class="d-flex flex-wrap gap-2">
                        @foreach ([
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'youtube' => 'YouTube',
        'telegram' => 'Telegram',
        'whatsapp' => 'WhatsApp',
    ] as $socialKey => $socialLabel)
                            @if (filled(setting('social_' . $socialKey)) &&
                                    filter_var(setting('social_' . $socialKey), FILTER_VALIDATE_URL) &&
                                    in_array(parse_url(setting('social_' . $socialKey), PHP_URL_SCHEME), ['http', 'https'], true))
                                <a href="{{ setting('social_' . $socialKey) }}" target="_blank"
                                    rel="noopener noreferrer" class="footer-social-link"
                                    aria-label="{{ $socialLabel }}" title="{{ $socialLabel }}">
                                    {{ $socialLabel }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>


            <div class="d-flex flex-row flex-wrap gap-2">
                <a class="btn btn-outline-light btn-sm" href="{{ route('index') }}#today-results">
                    Today's Results
                </a>

                <a class="btn btn-outline-light btn-sm" href="{{ route('index') }}#markets">
                    Market Board
                </a>

                <a class="btn btn-outline-light btn-sm" href="{{ route('index') }}#records">
                    Old Records
                </a>

                <a class="btn btn-outline-light btn-sm" href="{{ route('index') }}#market-charts">
                    Monthly Charts
                </a>

                @forelse ($navPages ?? collect() as $navPage)
                    <a class="btn btn-outline-light btn-sm" href="{{ route('page', ['page' => $navPage->slug]) }}">
                        {{ $navPage->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Copyright --}}
    <div class="footer-bottom border-top border-secondary">
        <div class="container py-3">
            <div class="row align-items-center g-2">
                <div class="col-md-8 text-center text-md-start">
                    <small class="text-white-50">
                        {{ setting('copyright_text', '© ' . date('Y') . ' ' . setting('title')) }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</footer>
<button type="button" class="wa-float" data-wa-open="" aria-label="Open WhatsApp contact">
    <span class="wa-pulse" aria-hidden="true"></span>
    <i class="bi bi-whatsapp fs-3"></i>
</button>
<span class="wa-label" aria-hidden="true">WhatsApp</span>
<div class="wa-modal" id="wa-modal">
    <div class="wa-modal-card">
        <div class="wa-modal-head">
            <span class="wa-modal-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="26"
                    height="26" fill="currentColor" aria-hidden="true">
                    <path
                        d="M24 4C12.95 4 4 12.95 4 24c0 3.55.93 6.9 2.56 9.8L4 44l10.44-2.53A19.87 19.87 0 0 0 24 44c11.05 0 20-8.95 20-20S35.05 4 24 4zm0 36c-3.18 0-6.17-.87-8.73-2.38l-.62-.37-6.2 1.5 1.55-5.98-.4-.65A15.93 15.93 0 0 1 8 24c0-8.82 7.18-16 16-16s16 7.18 16 16-7.18 16-16 16zm8.73-11.9c-.48-.24-2.83-1.4-3.27-1.56-.44-.16-.76-.24-1.08.24-.32.48-1.24 1.56-1.52 1.88-.28.32-.56.36-1.04.12-.48-.24-2.02-.74-3.85-2.36-1.42-1.27-2.38-2.83-2.66-3.31-.28-.48-.03-.74.21-.98.22-.22.48-.56.72-.84.24-.28.32-.48.48-.8.16-.32.08-.6-.04-.84-.12-.24-1.08-2.6-1.48-3.56-.39-.93-.78-.8-1.08-.82-.28-.01-.6-.01-.92-.01-.32 0-.84.12-1.28.6-.44.48-1.68 1.64-1.68 4s1.72 4.64 1.96 4.96c.24.32 3.38 5.16 8.2 7.24 1.15.5 2.04.8 2.74 1.02 1.15.36 2.2.31 3.02.19.92-.14 2.83-1.16 3.23-2.28.4-1.12.4-2.08.28-2.28-.12-.2-.44-.32-.92-.56z">
                    </path>
                </svg></span>
            <div><strong>Online Game Timing</strong>
                <div class="small">Tap below to chat on WhatsApp</div>
            </div>
            <button type="button" class="wa-modal-close" data-wa-close="" aria-label="Close">×</button>
        </div>
        <div class="wa-modal-body">
            <p class="muted small" style="margin:0 0 8px;letter-spacing:.08em;text-transform:uppercase">Game Schedule
            </p>
            <div class="wa-game-row"><span>Delhi Bazaar</span><span>03:15 PM</span></div>
            <div class="wa-game-row"><span>Shree Ganesh</span><span>04:45 PM</span></div>
            <div class="wa-game-row"><span>Faridabad</span><span>06:15 PM</span></div>
            <div class="wa-game-row"><span>Ghaziabad</span><span>10:00 PM</span></div>
            <div class="wa-game-row"><span>Gali</span><span>11:50 PM</span></div>
            <div class="wa-game-row"><span>Disawar</span><span>05:15 AM</span></div>
            <div class="wa-rates">
                <div><span>Jodi</span><strong>10 Ka 950</strong></div>
                <div><span>Haruf</span><strong>100 Ka 950</strong></div>
            </div>
        </div>
        <a class="wa-modal-cta"
            href="https://wa.me/919589836730?text=Namaste%21%20Main%20sattakhaiwal%20se%20online%20game%20ke%20baare%20mein%20jaankari%20chahta%20hoon."
            target="_blank" rel="noopener"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="18"
                height="18" fill="currentColor" aria-hidden="true">
                <path
                    d="M24 4C12.95 4 4 12.95 4 24c0 3.55.93 6.9 2.56 9.8L4 44l10.44-2.53A19.87 19.87 0 0 0 24 44c11.05 0 20-8.95 20-20S35.05 4 24 4zm0 36c-3.18 0-6.17-.87-8.73-2.38l-.62-.37-6.2 1.5 1.55-5.98-.4-.65A15.93 15.93 0 0 1 8 24c0-8.82 7.18-16 16-16s16 7.18 16 16-7.18 16-16 16zm8.73-11.9c-.48-.24-2.83-1.4-3.27-1.56-.44-.16-.76-.24-1.08.24-.32.48-1.24 1.56-1.52 1.88-.28.32-.56.36-1.04.12-.48-.24-2.02-.74-3.85-2.36-1.42-1.27-2.38-2.83-2.66-3.31-.28-.48-.03-.74.21-.98.22-.22.48-.56.72-.84.24-.28.32-.48.48-.8.16-.32.08-.6-.04-.84-.12-.24-1.08-2.6-1.48-3.56-.39-.93-.78-.8-1.08-.82-.28-.01-.6-.01-.92-.01-.32 0-.84.12-1.28.6-.44.48-1.68 1.64-1.68 4s1.72 4.64 1.96 4.96c.24.32 3.38 5.16 8.2 7.24 1.15.5 2.04.8 2.74 1.02 1.15.36 2.2.31 3.02.19.92-.14 2.83-1.16 3.23-2.28.4-1.12.4-2.08.28-2.28-.12-.2-.44-.32-.92-.56z">
                </path>
            </svg> Online Khelne Ke Liye Contact Karein</a>
    </div>
</div>
<style>
    .wa-float:hover {
        transform: scale(1.1);
    }

    .wa-float {
        position: fixed;
        bottom: 20px;
        left: 20px;
        z-index: 9999;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #25d366;
        color: #fff;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(0, 0, 0, .35);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    /* ==============================
   Premium Bootstrap Footer
================================ */

    .site-footer {
        background: #101827 !important;
        color: #f8fafc;
        overflow: hidden;
    }

    .site-footer .footer-description {
        max-width: 450px;
        font-size: 0.95rem;
    }

    .site-footer .footer-heading {
        position: relative;
        display: inline-block;
        color: #ffffff;
        font-size: 1.1rem;
        font-weight: 700;
        padding-bottom: 12px;
    }

    .site-footer .footer-heading::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: 0;
        width: 42px;
        height: 3px;
        border-radius: 10px;
        background: #22c55e;
    }

    .site-footer .footer-contact-link {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        color: #cbd5e1;
        text-decoration: none;
        overflow-wrap: anywhere;
        transition: color 0.25s ease;
    }

    .site-footer .footer-contact-link:hover {
        color: #22c55e;
    }

    .site-footer .footer-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 36px;
        width: 36px;
        height: 36px;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.05);
        color: #4ade80;
        font-size: 18px;
    }

    .site-footer .footer-nav-link {
        position: relative;
        display: inline-block;
        color: #cbd5e1;
        text-decoration: none;
        transition: color 0.25s ease, transform 0.25s ease;
    }

    .site-footer .footer-nav-link::before {
        content: "›";
        margin-right: 10px;
        color: #22c55e;
        font-size: 18px;
        font-weight: 700;
    }

    .site-footer .footer-nav-link:hover {
        color: #4ade80;
        transform: translateX(4px);
    }

    .site-footer .footer-social-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 9px 13px;
        border: 1px solid rgba(255, 255, 255, 0.13);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.04);
        color: #e2e8f0;
        font-size: 0.82rem;
        text-decoration: none;
        transition: all 0.25s ease;
    }

    .site-footer .footer-social-link:hover {
        background: #22c55e;
        border-color: #22c55e;
        color: #07130b;
        transform: translateY(-3px);
    }

    .site-footer .footer-notice {
        border: 1px solid rgba(250, 204, 21, 0.2);
        background: rgba(250, 204, 21, 0.055);
    }

    .site-footer .footer-notice-icon {
        color: #facc15;
        font-size: 23px;
        line-height: 1;
    }

    .site-footer .footer-bottom {
        background: rgba(0, 0, 0, 0.18);
    }

    .site-footer .footer-bottom-link {
        color: #cbd5e1;
        text-decoration: none;
        font-size: 0.85rem;
    }

    .site-footer .footer-bottom-link:hover {
        color: #4ade80;
    }

    .site-footer a:focus-visible {
        outline: 2px solid #4ade80;
        outline-offset: 4px;
    }

    @media (max-width: 767.98px) {
        .site-footer {
            text-align: left;
        }

        .site-footer .footer-description {
            max-width: 100%;
        }

        .site-footer .footer-social-link {
            padding: 8px 10px;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .site-footer *,
        .site-footer *::before,
        .site-footer *::after {
            transition: none !important;
            transform: none !important;
        }
    }
</style>
