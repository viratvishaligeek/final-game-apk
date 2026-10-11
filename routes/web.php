<?php

use App\Http\Controllers\Frontend\MainController;
use App\Http\Controllers\Frontend\AppDownloadController;
use App\Http\Controllers\Frontend\CommunityAuthController;
use App\Http\Controllers\Frontend\CommunityController;
use App\Http\Middleware\EnsureCommunityUser;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\WelcomeController;
use App\Models\Game;
use App\Models\Result;
use Illuminate\Support\Facades\DB;


require 'admin.php';
require 'extra.php';
require 'community.php';

Route::get('/download-app', AppDownloadController::class)->name('frontend.app-download');

Route::controller(MainController::class)->group(function () {
    Route::get('/', 'index')->name('index');

    // New stable, slug-only public game URL and the CSRF-protected AJAX endpoint.
    Route::get('/result/{slug}', 'market')->where('slug', '[A-Za-z0-9-]+')->name('frontend.market');
    Route::post('/get_result', 'chart')->middleware('throttle:60,1')->name('frontend.chart');

    // Backward-compatible redirects for established URLs; month/year are preserved
    // as noncanonical query parameters on the new game page where applicable.
    Route::get('/markets/{slug}', function (string $slug) {
        $game = \App\Models\Game::query()->where('status', 'active')->where('slug', $slug)->firstOrFail();

        return redirect()->route('frontend.market', ['slug' => $game->slug], 301);
    })->where('slug', '[A-Za-z0-9-]+')->name('frontend.legacy-market');

    Route::get('/charts/{slug}', function (string $slug) {
        $game = \App\Models\Game::query()->where('status', 'active')->where('slug', $slug)->firstOrFail();

        return redirect()->route('frontend.market', ['slug' => $game->slug], 301);
    })->where('slug', '[A-Za-z0-9-]+')->name('frontend.legacy-chart-root');

    Route::get('/charts/{slug}/{year}', function (string $slug, string $year) {
        $game = \App\Models\Game::query()->where('status', 'active')->where('slug', $slug)->firstOrFail();
        $yearNumber = (int) $year;
        $now = now()->timezone(config('app.timezone'));
        abort_if($yearNumber < 2000 || $yearNumber > (int) $now->format('Y'), 404);
        $months = app(\App\Services\MonthlyChartService::class)->availableMonths($game->id);
        abort_unless(isset($months[$yearNumber]) && $months[$yearNumber] !== [], 404);

        return redirect()->route('frontend.market', [
            'slug' => $game->slug,
            'year' => $yearNumber,
            'month' => $months[$yearNumber][0],
        ], 301);
    })->where(['slug' => '[A-Za-z0-9-]+', 'year' => '[0-9]{4}'])->name('frontend.legacy-chart-year');

    Route::get('/charts/{slug}/year/{year}', function (string $slug, string $year) {
        $game = \App\Models\Game::query()->where('status', 'active')->where('slug', $slug)->firstOrFail();
        $yearNumber = (int) $year;
        abort_if($yearNumber < 2000 || $yearNumber > (int) now()->timezone(config('app.timezone'))->format('Y'), 404);
        $months = app(\App\Services\MonthlyChartService::class)->availableMonths($game->id);
        abort_unless(isset($months[$yearNumber]) && $months[$yearNumber] !== [], 404);

        return redirect()->route('frontend.market', [
            'slug' => $game->slug,
            'year' => $yearNumber,
            'month' => $months[$yearNumber][0],
        ], 301);
    })->where(['slug' => '[A-Za-z0-9-]+', 'year' => '[0-9]{4}'])->name('frontend.year-chart');

    Route::get('/charts/{slug}/{year}/{month}', function (string $slug, string $year, string $month) {
        $game = \App\Models\Game::query()->where('status', 'active')->where('slug', $slug)->firstOrFail();
        $now = now()->timezone(config('app.timezone'));
        $yearNumber = (int) $year;
        $monthNumber = (int) $month;
        abort_if($yearNumber < 2000 || $yearNumber > (int) $now->format('Y') || $monthNumber < 1 || $monthNumber > 12, 404);
        abort_if($yearNumber === (int) $now->format('Y') && $monthNumber > (int) $now->format('n'), 404);
        abort_unless(\App\Models\Result::query()->where('game_id', $game->id)->where('type', 'jodi')
            ->whereBetween('game_date', [sprintf('%04d-%02d-01', $yearNumber, $monthNumber), \Carbon\Carbon::createFromDate($yearNumber, $monthNumber, 1, config('app.timezone'))->endOfMonth()->toDateString()])
            ->whereNotNull('number')->where('number', '<>', '')->exists(), 404);

        return redirect()->route('frontend.market', ['slug' => $game->slug, 'year' => $yearNumber, 'month' => $monthNumber], 301);
    })->where(['slug' => '[A-Za-z0-9-]+', 'year' => '[0-9]{4}', 'month' => '[0-9]{1,2}'])->name('frontend.legacy-month-chart');

    Route::get('/chart/{year}/{month}/{slug}', 'legacyChart')
        ->where(['year' => '[0-9]{4}', 'month' => '[0-9]{1,2}', 'slug' => '[A-Za-z0-9-]+'])
        ->name('frontend.legacy-chart');

    Route::get('/info/{page}', function (string $page) {
        abort_unless(in_array($page, ['about', 'contact', 'faq', 'privacy-policy', 'terms-and-conditions', 'disclaimer'], true), 404);

        return redirect()->route('information', ['page' => $page], 301);
    })->whereIn('page', ['about', 'contact', 'faq', 'privacy-policy', 'terms-and-conditions', 'disclaimer'])->name('frontend.legacy-information');

    // Keep additional CMS pages available without allowing arbitrary one-segment URLs.
    Route::get('/chart/{year}/{month}/{slug}', 'legacyChart')->where(['year' => '[0-9]{4}', 'month' => '[0-9]{1,2}', 'slug' => '[A-Za-z0-9-]+'])->name('frontend.legacy-chart');
    Route::get('/info/{page}', 'information')->whereIn('page', ['about', 'contact', 'faq', 'privacy-policy', 'terms-and-conditions', 'disclaimer'])->name('information');
    Route::get('/pages/{slug}', 'showPage')->where('slug', '[A-Za-z0-9-]+')->name('frontend.page');

    Route::get('/{page}', 'information')
        ->whereIn('page', ['about', 'contact', 'faq', 'privacy-policy', 'terms-and-conditions', 'disclaimer'])
        ->name('information');

});
