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

Route::get('/download-app', AppDownloadController::class)->name('frontend.app-download');

Route::get('/guessing', [CommunityController::class, 'index'])->name('community.index');
Route::get('/guessing/{post}', [CommunityController::class, 'show'])->whereNumber('post')->name('community.show');
Route::get('/community/login', [CommunityAuthController::class, 'loginForm'])->name('community.login');
Route::post('/community/login', [CommunityAuthController::class, 'login'])->middleware('throttle:10,1')->name('community.login.submit');
Route::get('/community/register', [CommunityAuthController::class, 'registerForm'])->name('community.register');
Route::post('/community/register', [CommunityAuthController::class, 'register'])->middleware('throttle:5,10')->name('community.register.submit');
Route::middleware(EnsureCommunityUser::class)->group(function () {
    Route::get('/guessing/create', [CommunityController::class, 'create'])->name('community.create');
    Route::post('/guessing', [CommunityController::class, 'store'])->middleware('throttle:5,1')->name('community.store');
    Route::post('/guessing/{post}/replies', [CommunityController::class, 'reply'])->whereNumber('post')->middleware('throttle:10,1')->name('community.reply');
    Route::post('/community/logout', [CommunityAuthController::class, 'logout'])->name('community.logout');
});

Route::controller(MainController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/markets/{slug}', 'market')->where('slug', '[A-Za-z0-9-]+')->name('frontend.market');
    Route::get('/charts/{slug}/year/{year}', 'yearChart')->where(['slug' => '[A-Za-z0-9-]+', 'year' => '[0-9]{4}'])->name('frontend.year-chart');
    Route::get('/charts/{slug}/{year?}/{month?}', 'chart')->where(['slug' => '[A-Za-z0-9-]+', 'year' => '[0-9]{4}', 'month' => '[0-9]{1,2}'])->name('frontend.chart');

Route::get('/chart/{year}/{month}/{slug}', 'legacyChart')->where(['year' => '[0-9]{4}', 'month' => '[0-9]{1,2}', 'slug' => '[A-Za-z0-9-]+'])->name('frontend.legacy-chart');
    Route::get('/info/{page}', 'information')->whereIn('page', ['about', 'contact', 'faq', 'privacy-policy', 'terms-and-conditions', 'disclaimer'])->name('information');
    Route::get('/pages/{slug}', 'showPage')->where('slug', '[A-Za-z0-9-]+')->name('frontend.page');
});

