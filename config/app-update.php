<?php

$defaultHost = parse_url((string) env('APP_URL', 'https://playonlinekhaiwal.com'), PHP_URL_HOST);

return [
    /*
    | Hosts allowed for administrator-configured APK downloads. Keep this list
    | explicit so a compromised settings account cannot point users at an
    | arbitrary HTTPS host. Comma-separated hostnames, no schemes or paths.
    */
    'allowed_hosts' => array_values(array_filter(array_map(
        static fn (string $host): string => strtolower(trim($host)),
        explode(',', (string) env('APP_UPDATE_ALLOWED_HOSTS', $defaultHost ?: 'playonlinekhaiwal.com'))
    ))),
];
