<?php

use App\Http\Controllers\Frontend\MainController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\WelcomeController;


require 'admin.php';
Route::get('/clear', function () {
    $exitCode = Artisan::call('optimize:clear');
    return '<h1>Optimize Cleared Now</h1>';
});

Route::controller(MainController::class)->group(function () {
    Route::get('/', 'index')->name('index');
});
