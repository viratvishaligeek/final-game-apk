<?php

use App\Http\Controllers\Frontend\CommunityAuthController;
use App\Http\Controllers\Frontend\CommunityController;
use App\Http\Middleware\EnsureCommunityUser;
use Illuminate\Support\Facades\Route;

Route::controller(CommunityController::class)->name('community.')->group(function () {
    Route::get('/guessing', 'index')->name('index');
    Route::get('/guessing/{post}', 'show')->whereNumber('post')->name('show');
});

Route::controller(CommunityAuthController::class)->prefix('community')->name('community.')->group(function () {
    Route::get('/login', 'loginForm')->name('login');
    Route::post('/login', 'login')->middleware('throttle:10,1')->name('login.submit');
    Route::get('/register', 'registerForm')->name('register');
    Route::post('/register', 'register')->middleware('throttle:5,10')->name('register.submit');
});

Route::middleware(EnsureCommunityUser::class)->prefix('community')->name('community.')->group(function () {
    Route::get('/guessing/create', [CommunityController::class, 'create'])->name('create');
    Route::post('/guessing', [CommunityController::class, 'store'])->middleware('throttle:5,1')->name('store');
    Route::post('/guessing/{post}/replies', [CommunityController::class, 'reply'])->whereNumber('post')->middleware('throttle:10,1')->name('reply');
    Route::post('/community/logout', [CommunityAuthController::class, 'logout'])->name('logout');
});
