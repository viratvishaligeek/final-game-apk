<?php

use App\Http\Controllers\Frontend\MainController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\WelcomeController;
use App\Models\Game;
use App\Models\Result;
use Illuminate\Support\Facades\DB;


require 'admin.php';
require 'extra.php';

Route::controller(MainController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/markets/{slug}', 'market')->name('frontend.market');
    Route::get('/charts/{slug}/{year?}', 'chart')->whereNumber('year')->name('frontend.chart');
    Route::get('/info/{page}', 'information')->whereIn('page', ['about', 'contact', 'faq', 'privacy-policy', 'terms-and-conditions', 'disclaimer'])->name('information');
});

