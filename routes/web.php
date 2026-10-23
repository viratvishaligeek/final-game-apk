<?php

use App\Http\Controllers\Frontend\MainController;
use App\Http\Controllers\Frontend\AppDownloadController;
use Illuminate\Support\Facades\Route;


require 'admin.php';
require 'extra.php';
require 'community.php';

Route::get('/download-app', AppDownloadController::class)->name('frontend.app-download');

Route::controller(MainController::class)->group(function () {
    Route::get('/', 'index')->name('index');

    Route::get('/result/{slug}', 'market')->where('slug', '[A-Za-z0-9-]+')->name('frontend.market');
    Route::post('/get_result', 'chart')->middleware('throttle:60,1')->name('frontend.chart');
    Route::get('/{page}', 'information')->where('slug', '[A-Za-z0-9-]+')->name('information');
});
