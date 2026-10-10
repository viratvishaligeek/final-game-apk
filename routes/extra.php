<?php

use App\Models\Game;
use App\Models\Result;
use App\Models\Page;
use App\Services\MonthlyChartService;
use App\Services\AppSettingsService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/firebase-config.js', function () {
    $web = config('services.fcm_web');
    $firebaseConfig = [
        'apiKey' => $web['api_key'] ?? '',
        'authDomain' => $web['auth_domain'] ?? '',
        'projectId' => $web['project_id'] ?? '',
        'messagingSenderId' => $web['messaging_sender_id'] ?? '',
        'appId' => $web['app_id'] ?? '',
    ];

    $javascript = 'self.FIREBASE_CONFIG = ' . json_encode($firebaseConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';'
        . 'self.FIREBASE_VAPID_KEY = ' . json_encode($web['vapid_key'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';';

    return response($javascript, 200, [
        'Content-Type' => 'application/javascript; charset=UTF-8',
        'Cache-Control' => 'no-store, max-age=0',
    ]);
});
Route::get('/firebase-config.json', function () {
    $web = config('services.fcm_web');

    return response()->json([
        'apiKey' => $web['api_key'] ?? '',
        'authDomain' => $web['auth_domain'] ?? '',
        'projectId' => $web['project_id'] ?? '',
        'messagingSenderId' => $web['messaging_sender_id'] ?? '',
        'appId' => $web['app_id'] ?? '',
        'vapidKey' => $web['vapid_key'] ?? '',
    ])->header('Cache-Control', 'no-store, max-age=0');
});

Route::get('/optimize-clear', function () {
    Artisan::call('optimize:clear');

    return response()->json([
        'success' => true,
        'message' => 'Application cache cleared successfully.',
    ]);
});

Route::get('/storage-link', function () {
    Artisan::call('storage:link');

    return response()->json([
        'success' => true,
        'message' => 'Storage link created successfully.',
    ]);
});

Route::get('/sitemap.xml', function () {
    $base = rtrim((string) app(AppSettingsService::class)->value('canonical_url', 'https://playonlinekhaiwal.com'), '/');
    $urls = [$base . '/'];

    foreach (['about', 'contact', 'faq', 'privacy-policy', 'terms-and-conditions', 'disclaimer'] as $page) {
        $urls[] = $base . '/info/' . $page;
    }

    $charts = app(MonthlyChartService::class);
    $games = Game::query()->where('status', 'active')->orderBy('serial')->get(['id', 'slug']);
    foreach ($games as $game) {
        $monthsByYear = $charts->availableMonths($game->id);
        foreach ($monthsByYear as $year => $months) {
            foreach ($months as $month) {
                $urls[] = $base . '/charts/' . rawurlencode($game->slug) . '/' . (int) $year . '/' . (int) $month;
            }
        }
    }

    $pages = Page::query()
        ->where('status', 'active')
        ->where('noindex', false)
        ->orderBy('id')
        ->get(['slug']);
    foreach ($pages as $page) {
        $urls[] = $base . '/pages/' . rawurlencode($page->slug);
    }

    $urls = array_values(array_unique($urls));
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $loc) {
        $xml .= '<url><loc>' . htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc></url>';
    }
    $xml .= '</urlset>';

    return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('sitemap');
