@php
    $whatsappUrl = trim((string) setting('social_whatsapp', ''));
    $telegramUrl = trim((string) setting('social_telegram', ''));
    $safeWhatsapp = filter_var($whatsappUrl, FILTER_VALIDATE_URL) && in_array(parse_url($whatsappUrl, PHP_URL_SCHEME), ['http', 'https'], true);
    $safeTelegram = filter_var($telegramUrl, FILTER_VALIDATE_URL) && in_array(parse_url($telegramUrl, PHP_URL_SCHEME), ['http', 'https'], true);
@endphp
@if ($safeWhatsapp)
    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="sticky-social sticky-social--whatsapp" aria-label="WhatsApp">
        <span>WhatsApp</span>
    </a>
@endif
@if ($safeTelegram)
    <a href="{{ $telegramUrl }}" target="_blank" rel="noopener noreferrer" class="sticky-social sticky-social--telegram" aria-label="Telegram">
        <span>Telegram</span>
    </a>
@endif
