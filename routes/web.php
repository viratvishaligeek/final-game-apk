<?php

use App\Http\Controllers\Frontend\MainController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\WelcomeController;


require 'admin.php';

// Firebase web config contains public SDK identifiers only. Service-account
// credentials remain server-side in FCM_SERVICE_ACCOUNT_JSON.
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

Route::get('/clear', function () {
    $exitCode = Artisan::call('optimize:clear');
    return '<h1>Optimize Cleared Now</h1>';
});

Route::get('/sitemap.xml', function () {
    $urls = [url('/')];
    foreach (['about', 'contact', 'faq', 'privacy-policy', 'terms-and-conditions', 'disclaimer'] as $page) {
        $urls[] = route('information', ['page' => $page]);
    }
    $games = Game::query()->where('status', 'active')->orderBy('serial')->get(['slug']);
    $yearExpression = \\Illuminate\\Support\\Facades\\DB::connection()->getDriverName() === 'sqlite'
        ? "CAST(strftime('%Y', game_date) AS INTEGER)" : 'YEAR(game_date)';
    foreach ($games as $game) {
        $urls[] = route('frontend.market', ['slug' => $game->slug]);
        $years = Result::query()->where('game_id', $game->id)->where('type', 'jodi')->whereNotNull('game_date')
            ->selectRaw("{$yearExpression} as year")->distinct()->pluck('year');
        foreach ($years as $year) {
            $urls[] = route('frontend.chart', ['slug' => $game->slug, 'year' => (int) $year]);
        }
    }
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach (array_unique($urls) as $loc) {
        $xml .= '<url><loc>' . htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc></url>';
    }
    $xml .= '</urlset>';
    return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('sitemap');

Route::controller(MainController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/markets/{slug}', 'market')->name('frontend.market');
    Route::get('/charts/{slug}/{year?}', 'chart')->whereNumber('year')->name('frontend.chart');
    Route::get('/info/{page}', 'information')->whereIn('page', ['about', 'contact', 'faq', 'privacy-policy', 'terms-and-conditions', 'disclaimer'])->name('information');
});


// Route::get('/optimize-clear', function () {
//     Artisan::call('optimize:clear');

//     return response()->json([
//         'success' => true,
//         'message' => 'Application cache cleared successfully.',
//     ]);
// });

Route::get('/storage-link', function () {
    Artisan::call('storage:link');

    return response()->json([
        'success' => true,
        'message' => 'Storage link created successfully.',
    ]);
});
