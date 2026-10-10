<?php

use App\Http\Controllers\Frontend\MainController;
use App\Http\Controllers\Frontend\AppDownloadController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\WelcomeController;
use App\Models\Game;
use App\Models\Result;
use Illuminate\Support\Facades\DB;


require 'admin.php';
require 'extra.php';

Route::get('/download-app', AppDownloadController::class)->name('frontend.app-download');

Route::controller(MainController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/markets/{slug}', 'market')->name('frontend.market');
    Route::get('/charts/{slug}/{year?}/{month?}', 'chart')->whereNumber('year')->whereNumber('month')->name('frontend.chart');
    Route::get('/info/{page}', 'information')->whereIn('page', ['about', 'contact', 'faq', 'privacy-policy', 'terms-and-conditions', 'disclaimer'])->name('information');
    Route::get('/pages/{slug}', 'showPage')->where('slug', '[A-Za-z0-9-]+')->name('frontend.page');
});

