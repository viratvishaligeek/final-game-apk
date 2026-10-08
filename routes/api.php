<?php

use App\Http\Controllers\Api\ResultChartController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\BidController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Middleware\EnsureUserIsActive;

Route::prefix('v1')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('login', 'tryLogin');
        Route::post('register', 'register');
        Route::post('forgot/send-otp', 'sendPasswordResetOtp');
        Route::post('forgot/reset', 'resetPasswordWithOtp');
    });

    // after login routes
    Route::middleware(['auth:sanctum', EnsureUserIsActive::class])->group(function () {
        Route::controller(DashboardController::class)->group(function () {
            Route::get('dashboard', 'getDashboard');
            Route::post('logout', 'logout');
            Route::get('user', 'user');
            Route::put('/update-profile', 'updateProfile');
            Route::put('/update-password', 'changePassword');
            Route::get('/notifications', 'notificationList');
            Route::get('/settings', 'getSetting');
        });

        Route::controller(WalletController::class)->prefix('wallet')->group(function () {
            Route::get('/', 'index');
            Route::get('/payment-methods', 'paymentMethods');
            Route::post('/add-money-request', 'addMoneyRequest');
            Route::post('/gateway/create-order', 'createGatewayOrder');
            Route::post('/withdraw', 'withdraw');
            Route::prefix('gateway')->group(function () {
                Route::get('/return', 'gatewayReturn');
                Route::post('/webhook', 'gatewayWebhook');
            });
            Route::get('/get-money-request', 'getMoneyRequest');
        });

        Route::controller(GameController::class)->prefix('games')->group(function () {
            Route::get('/list', 'index',);
            Route::get('/{game}', 'show',);
            Route::post('/{game}/bids', [BidController::class, 'store',]);
        });

        Route::get('/play-history', [BidController::class, 'history']);

        Route::controller(ResultChartController::class)->prefix('result-chart')->group(function () {
            Route::get('/index', 'index',);
        });
        Route::get('/pages/{slug}', [PageController::class, 'show']);

    });
});
