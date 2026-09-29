<?php

use App\Http\Controllers\Backend\BiddingDeskController;
use App\Http\Controllers\Backend\MemberController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\WalletController;
use App\Http\Controllers\Backend\WinnerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\Auth\AuthController;
use App\Http\Controllers\Backend\BannerController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\FaqController;
use App\Http\Controllers\Backend\GameController;
use App\Http\Controllers\Backend\PagesController;
use App\Http\Controllers\Backend\ResultController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\WalletRequestController;

// ------------------Admin Routes-------------------------------------
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::post('/try-login', 'tryLogin')->name('try_login');
    Route::get('/forgot_password', 'forgetPassword')->name('forgot_password');
    Route::get('/recoverPassword', 'velidateEmail')->name('recoverPassword');
});

// After Login
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth:admin'], function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('dashboard', 'dashboard')->name('dashboard');
        Route::get('/profile', 'profile')->name('profile');
        Route::post('/update-profile', 'update')->name('update-profile');
        Route::post('/update-password', 'updatePassword')->name('update-password');
        Route::get('/logout', 'logout')->name('logout');
    });

    Route::resource('games', GameController::class);
    Route::resource('pages', PagesController::class);

    Route::resource('users', UserController::class);
    Route::patch('/users/toggle-status/{id}', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    Route::controller(WalletController::class)->prefix('wallet')->name('wallet.')->group(function () {
        Route::post('/debit/{id}', 'debit')->name('debit');
        Route::post('/credit/{id}', 'credit')->name('credit');

        Route::controller(WalletRequestController::class)->group(function () {
            Route::get('/request-add', 'addRequests')->name('request-add');
            Route::get('/request-withdraw', 'withdrawRequests')->name('request-withdraw');
            Route::get('/request/{walletRequest}', 'show')->name('request-show');
            Route::post('/request/{walletRequest}/approve', 'approve')->name('request.approve');
            Route::post('/request/{walletRequest}/reject', 'reject')->name('request.reject');
        });
    });
    Route::controller(ResultController::class)->prefix('results')->name('results.')->group(function () {
        Route::get('/results', 'index')->name('index');
        Route::post('/results/save', 'storeOrUpdate')->name('storeOrUpdate');
        Route::post('/results/revert', 'revert')->name('revert');
    });

    Route::resource('member', MemberController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('faqs', FaqController::class);
    Route::resource('banner', BannerController::class);

    Route::resource('winner', WinnerController::class);

    Route::controller(BiddingDeskController::class)->prefix('bidding-desk')->name('bidding-desk.')->group(function () {
        Route::get('/index', 'index')->name('index');
        Route::get('/data', 'data')->name('data');
        Route::get('/details', 'details')->name('details');
    });
    // Route::controller(PagesController::class)->group(function () { // dashboard routes
    //     Route::get('homepage', 'homepage')->name('homepage');
    //     Route::post('homepage/store', 'homepageStore')->name('homepage.store');
    //     Route::post('homepage/edit', 'homepageEdit')->name('homepage.edit');
    //     Route::post('homepage/update', 'homepageUpdate')->name('homepage.update');
    // });

    // Route::resource('setting', SettingController::class);

    // Route::controller(GameHistoryController::class)->group(function () { // dashboard routes
    //     Route::get('/profit-loss-history', 'profitLossHistory')->name('profit_loss_history');
    // });

    // Route::controller(UtilityController::class)->group(function () { // dashboard routes
    //     Route::group(['prefix' => 'application', 'as' => 'application.'], function () {
    //         Route::get('/utilities', 'utilities')->name('utilities');
    //         Route::post('/update-utilities', 'updateUtilities')->name('update_utilities');
    //         Route::get('/slider', 'slider')->name('slider');
    //         Route::post('/store-slider', 'storeSlider')->name('store_slider');
    //         Route::delete('/delete-slider/{id}', 'deleteSlider')->name('delete_slider');
    //         Route::get('/spinner', 'spinner')->name('spinner');
    //         Route::post('/store-spinner', 'storeSpinner')->name('store_spinner');
    //         Route::delete('/delete-spinner/{id}', 'deleteSpinner')->name('delete_spinner');
    //     });
    // });
});
