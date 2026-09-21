<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\WelcomeController;


require 'admin.php';
Route::get('/clear', function () {
    $exitCode = Artisan::call('optimize:clear');
    return '<h1>Optimize Cleared Now</h1>';
});

Route::controller(WelcomeController::class)->group(function () {
    Route::get('/', 'welcome')->name('index');
    Route::get('/page-data/{slug}', 'pageContent')->name('pageData');
    Route::get('/game-data/{slug}', 'gameContent')->name('gameData');
    Route::get('/game-date-2024/{slug}', 'oldGameContent')->name('old_game_date');
    Route::get('/game-function', 'gameFunctionImp')->name('game_function');
});
