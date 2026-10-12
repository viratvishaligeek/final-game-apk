<?php

$defaultKey = env('SITE_DEFAULT_KEY', 'play-online-khaiwal');

return [
    'default' => $defaultKey,
    'strict_hosts' => env('SITES_STRICT_HOSTS', env('APP_ENV', 'production') === 'production'),
    'current_key' => $defaultKey,
    'current' => [
        'key' => $defaultKey,
        'name' => env('APP_NAME', 'Play Online Khaiwal'),
        'url' => rtrim(env('APP_URL', 'https://playonlinekhaiwal.com'), '/'),
        'hosts' => ['playonlinekhaiwal.com', 'www.playonlinekhaiwal.com'],
    ],
    'sites' => [
        'play-online-khaiwal' => [
            'name' => 'Play Online Khaiwal',
            'url' => rtrim(env('SITE_PLAY_ONLINE_KHAIWAL_URL', env('APP_URL', 'https://playonlinekhaiwal.com')), '/'),
            'hosts' => ['playonlinekhaiwal.com', 'www.playonlinekhaiwal.com'],
        ],
        'galidisawar' => [
            'name' => 'Gali Disawar',
            'url' => rtrim(env('SITE_GALIDISAWAR_URL', 'https://galidisawar.com'), '/'),
            'hosts' => ['galidisawar.com', 'www.galidisawar.com'],
        ],
    ],
];
