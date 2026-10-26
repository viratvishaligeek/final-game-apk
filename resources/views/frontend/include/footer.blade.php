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
<button type="button" class="wa-float" data-bs-toggle="modal" data-bs-target="#whatsappContactModal"
    aria-label="Open WhatsApp contact" aria-controls="whatsappContactModal">
    <i class="bi bi-whatsapp fs-3" aria-hidden="true"></i>
</button>
<div class="modal fade" id="whatsappContactModal" tabindex="-1" aria-labelledby="whatsappContactModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 overflow-hidden shadow-lg">
            <div class="modal-header bg-success text-white border-0 p-4">
                <div class="d-flex align-items-center gap-3">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white text-success p-3 flex-shrink-0">
                        <i class="bi bi-whatsapp fs-3" aria-hidden="true"></i>
                    </span>
                    <div>
                        <h2 class="modal-title fs-5 fw-bold mb-1" id="whatsappContactModalLabel">Online Game Timing</h2>
                        <p class="small mb-0 text-white">Chat with us on WhatsApp</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-3" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-uppercase fw-bold text-secondary mb-3">Game schedule</p>
                <div class="list-group list-group-flush mb-4">
                    <div class="list-group-item d-flex justify-content-between gap-3 px-0"><span>Delhi Bazaar</span><strong>03:15 PM</strong></div>
                    <div class="list-group-item d-flex justify-content-between gap-3 px-0"><span>Shree Ganesh</span><strong>04:45 PM</strong></div>
                    <div class="list-group-item d-flex justify-content-between gap-3 px-0"><span>Faridabad</span><strong>06:15 PM</strong></div>
                    <div class="list-group-item d-flex justify-content-between gap-3 px-0"><span>Ghaziabad</span><strong>10:00 PM</strong></div>
                    <div class="list-group-item d-flex justify-content-between gap-3 px-0"><span>Gali</span><strong>11:50 PM</strong></div>
                    <div class="list-group-item d-flex justify-content-between gap-3 px-0"><span>Disawar</span><strong>05:15 AM</strong></div>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="rounded-3 bg-light p-3 h-100">
                            <span class="d-block small text-secondary mb-1">Jodi</span>
                            <strong>10 Ka 950</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="rounded-3 bg-light p-3 h-100">
                            <span class="d-block small text-secondary mb-1">Haruf</span>
                            <strong>100 Ka 950</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <a class="btn btn-success btn-lg w-100 d-flex align-items-center justify-content-center gap-2"
                    href="https://wa.me/919589836730?text=Namaste%21%20Main%20sattakhaiwal%20se%20online%20game%20ke%20baare%20mein%20jaankari%20chahta%20hoon."
                    target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-whatsapp" aria-hidden="true"></i>
                    Online Khelne Ke Liye Contact Karein
                </a>
            </div>
        </div>
    </div>
</div>
<style>
    .wa-float:hover {
        transform: scale(1.1);
    }

    .wa-float {
        position: fixed;
        bottom: calc(20px + env(safe-area-inset-bottom, 0px));
        left: 20px;
        z-index: 1040;
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

    .wa-float:focus-visible {\n        outline: 3px solid #128c4a;\n        outline-offset: 4px;\n    }\n\n    @media (max-width: 575.98px) {\n        .wa-float {\n            left: 14px;\n            bottom: calc(14px + env(safe-area-inset-bottom, 0px));\n            width: 54px;\n            height: 54px;\n        }\n    }\n\n    /* ==============================
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
