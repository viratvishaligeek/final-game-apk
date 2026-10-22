<?php

use App\Models\Game;
use App\Models\CommunityPost;
use App\Models\Page;
use App\Models\Result;
use App\Services\AppSettingsService;
use Illuminate\Support\Facades\Artisan;
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


Route::get('/storage-link', function () {
    Artisan::call('migrate --force');
    return response()->json([
        'success' => true,
        'message' => 'Forcly Migrated Successfully.',
    ]);
});


Route::get('/fresh-db', function () {
    Artisan::call('migrate:fresh');
    Artisan::call('db:seed');
    Artisan::call('permissions:setup');
    return response()->json([
        'success' => true,
        'message' => 'New Migration, New Seed, New Permissions setuped successfully.',
    ]);
});

$buildPublicSitemapEntries = function (): array {
    $base = rtrim((string) app(AppSettingsService::class)->value('canonical_url', config('app.url', 'https://playonlinekhaiwal.com')), '/');
    $entries = [$base . '/' => null];
    foreach (['about', 'contact', 'faq', 'privacy-policy', 'terms-and-conditions', 'disclaimer'] as $informationSlug) {
        $entries[$base . '/' . $informationSlug] = null;
    }

    $latestResultUpdates = Result::query()
        ->where('type', 'jodi')
        ->whereNotNull('number')
        ->where('number', '<>', '')
        ->whereDate('game_date', '<=', now()->timezone(config('app.timezone'))->toDateString())
        ->select('game_id')
        ->selectRaw('MAX(updated_at) as lastmod')
        ->groupBy('game_id')
        ->pluck('lastmod', 'game_id');

    Game::query()
        ->where('status', 'active')
        ->whereNotNull('slug')
        ->where('slug', '<>', '')
        ->orderBy('serial')
        ->orderBy('id')
        ->get(['id', 'slug'])
        ->each(function (Game $game) use (&$entries, $base, $latestResultUpdates) {
            $entries[$base . '/result/' . rawurlencode($game->slug)] = $latestResultUpdates->get($game->id);
        });

    if (CommunityPost::query()->where('status', 'approved')->exists()) {
        $entries[$base . '/guessing'] = null;
    }

    Page::query()
        ->where('status', 'active')
        ->where(function ($query) {
            $query->where('noindex', false)->orWhereNull('noindex');
        })
        ->orderBy('id')
        ->get(['slug', 'updated_at'])
        ->each(function (Page $page) use (&$entries, $base) {
            $entries[$base . '/pages/' . rawurlencode($page->slug)] = $page->updated_at;
        });

    return $entries;
};

$renderSitemapUrlSet = function (array $entries) {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($entries as $loc => $lastmod) {
        $xml .= '<url><loc>' . htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';
        if ($lastmod) {
            $xml .= '<lastmod>' . htmlspecialchars((is_string($lastmod) ? \Carbon\Carbon::parse($lastmod) : $lastmod)->copy()->utc()->toAtomString(), ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</lastmod>';
        }
        $xml .= '</url>';
    }
    $xml .= '</urlset>';

    return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
};

Route::get('/sitemap-{part}.xml', function (string $part) use ($buildPublicSitemapEntries, $renderSitemapUrlSet) {
    abort_unless(ctype_digit($part) && (int) $part >= 1, 404);
    $entries = $buildPublicSitemapEntries();
    $chunks = array_chunk($entries, 50000, true);
    abort_if((int) $part > count($chunks), 404);

    return $renderSitemapUrlSet($chunks[(int) $part - 1]);
})->where('part', '[0-9]+')->name('sitemap.part');

Route::get('/sitemap.xml', function () use ($buildPublicSitemapEntries, $renderSitemapUrlSet) {
    $entries = $buildPublicSitemapEntries();
    $chunks = array_chunk($entries, 50000, true);
    if (count($chunks) <= 1) {
        return $renderSitemapUrlSet($entries);
    }

    $base = rtrim((string) app(AppSettingsService::class)->value('canonical_url', config('app.url', 'https://playonlinekhaiwal.com')), '/');
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach (array_keys($chunks) as $index) {
        $xml .= '<sitemap><loc>' . htmlspecialchars($base . '/sitemap-' . ($index + 1) . '.xml', ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc></sitemap>';
    }
    $xml .= '</sitemapindex>';

    return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('sitemap');
