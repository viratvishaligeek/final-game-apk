@foreach ($sections as $section)
    <section class="bg-light py-4" id="homepage-section-{{ $section->id }}">
        <div class="container">
            <article class="card p-4">
                <p class="small text-uppercase fw-bold text-primary mb-2">{{ str_replace('_', ' ', $section->location) }}</p>
                <h2>{{ $section->title }}</h2>
                @if (filled($section->short_desc))
                    <div class="mb-3">{!! $section->short_desc !!}</div>
                @endif
                @if (filled($section->content))
                    <div class="mb-3">{!! $section->content !!}</div>
                @endif
                @if ($section->phone || $section->whatsapp || $section->telegram)
                    <div class="d-flex flex-wrap gap-2">
                        @if ($section->phone)
                            <a class="btn btn btn-dark"
                                href="tel:{{ preg_replace('/[^0-9+]/', '', $section->phone) }}">Call
                                {{ $section->phone }}</a>
                        @endif
                        @if ($section->whatsapp)
                            @php($whatsappDigits = preg_replace('/\D/', '', $section->whatsapp))
                            @php($whatsappUrl = filter_var($section->whatsapp, FILTER_VALIDATE_URL) && in_array(parse_url($section->whatsapp, PHP_URL_SCHEME), ['http', 'https'], true) ? $section->whatsapp : ($whatsappDigits !== '' ? 'https://wa.me/' . $whatsappDigits : null))
                            @if ($whatsappUrl)
                                <a class="btn btn btn-warning" href="{{ $whatsappUrl }}" target="_blank"
                                    rel="noopener noreferrer">WhatsApp</a>
                            @else
                                <span>{{ $section->whatsapp }}</span>
                            @endif
                        @endif
                        @if ($section->telegram)
                            @php($telegramUrl = filter_var($section->telegram, FILTER_VALIDATE_URL) && in_array(parse_url($section->telegram, PHP_URL_SCHEME), ['http', 'https'], true) ? $section->telegram : (preg_match('/^@?[A-Za-z0-9_]{5,32}$/', trim($section->telegram)) ? 'https://t.me/' . ltrim(trim($section->telegram), '@') : null))
                            @if ($telegramUrl)
                                <a class="btn btn btn-light" href="{{ $telegramUrl }}" target="_blank"
                                    rel="noopener noreferrer">Telegram</a>
                            @else
                                <span>{{ $section->telegram }}</span>
                            @endif
                        @endif
                    </div>
                @endif
            </article>
        </div>
    </section>
@endforeach
