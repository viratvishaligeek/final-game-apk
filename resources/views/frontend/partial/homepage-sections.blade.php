@foreach ($sections as $section)
    <section class="section-block section-light homepage-managed-section" id="homepage-section-{{ $section->id }}">
        <div class="wrap">
            @php($sectionBackground = preg_match('/^#[0-9a-fA-F]{3,8}$/', (string) $section->background) ? $section->background : null)
            @php($sectionBackgroundClass = preg_match('/^[A-Za-z0-9_-]+$/', (string) $section->background) ? $section->background : null)
            <article class="market-card {{ $sectionBackgroundClass ? $sectionBackgroundClass : '' }}" @if ($sectionBackground) style="background-color: {{ $sectionBackground }}" @endif>
                <p class="eyebrow">{{ str_replace('_', ' ', $section->location) }}</p>
                <h2>{{ $section->title }}</h2>
                @if (filled($section->short_desc))
                    <p>{{ $section->short_desc }}</p>
                @endif
                @if (filled($section->content))
                    <div class="homepage-managed-content">{!! \App\Support\SafeHtml::sanitize($section->content) !!}</div>
                @endif
                @if ($section->phone || $section->whatsapp || $section->telegram)
                    <div class="ribbon-actions">
                        @if ($section->phone)
                            <a class="button button-dark" href="tel:{{ preg_replace('/[^0-9+]/', '', $section->phone) }}">Call {{ $section->phone }}</a>
                        @endif
                        @if ($section->whatsapp)
                            @php($whatsappDigits = preg_replace('/\D/', '', $section->whatsapp))
                            @php($whatsappUrl = filter_var($section->whatsapp, FILTER_VALIDATE_URL) && in_array(parse_url($section->whatsapp, PHP_URL_SCHEME), ['http', 'https'], true) ? $section->whatsapp : ($whatsappDigits !== '' ? 'https://wa.me/' . $whatsappDigits : null))
                            @if ($whatsappUrl)
                                <a class="button button-gold" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                            @else
                                <span>{{ $section->whatsapp }}</span>
                            @endif
                        @endif
                        @if ($section->telegram)
                            @php($telegramUrl = filter_var($section->telegram, FILTER_VALIDATE_URL) && in_array(parse_url($section->telegram, PHP_URL_SCHEME), ['http', 'https'], true) ? $section->telegram : (preg_match('/^@?[A-Za-z0-9_]{5,32}$/', trim($section->telegram)) ? 'https://t.me/' . ltrim(trim($section->telegram), '@') : null))
                            @if ($telegramUrl)
                                <a class="button button-light" href="{{ $telegramUrl }}" target="_blank" rel="noopener noreferrer">Telegram</a>
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
