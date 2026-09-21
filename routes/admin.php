<?php

use App\Http\Controllers\Backend\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\Auth\AuthController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\GameController;
use App\Http\Controllers\Backend\PagesController;

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
        Route::get('/logout', 'logout')->name('logout');
        Route::get('total-winner', 'totalWinner')->name('total_winner');
    });

    Route::resource('games', GameController::class);
    // Route::get('/view-bid/{id}', 'viewBid')->name('view_bid');
    // Route::get('/view-winner/{id}', 'viewWinner')->name('view_winner');

     Route::controller(ResultController::class)->group(function () { // dashboard routes
        Route::get('/today_result', 'index')->name('today_result');
        Route::post('edit_result', 'edit')->name('edit_result');
        Route::post('update_result', 'update')->name('update_result');
        Route::post('reset_result', 'delete')->name('reset_result');
        Route::post('addfunction', 'addfunction')->name('addfunction');
    });

    Route::resource('users', UserController::class);
    Route::patch('/users/toggle-status/{id}', [UserController::class,'toggleStatus'])->name('users.toggle-status');
    // Route::get('/transaction_history/{id}', 'transactionHistory')->name('transaction_history');
    // Route::get('/add_money/{id}', 'addMoney')->name('add_money');
    // Route::post('/try_add_money/{id}', 'tryAddMoney')->name('try_add_money');

    Route::resource('pages', PagesController::class);




    // Route::controller(GameResultController::class)->group(function () { // dashboard routes
    //     Route::get('/today_result', 'index')->name('today_result');
    //     Route::post('edit_result', 'edit')->name('edit_result');
    //     Route::post('update_result', 'update')->name('update_result');
    //     Route::post('reset_result', 'delete')->name('reset_result');
    //     Route::post('addfunction', 'addfunction')->name('addfunction');
    // });

    // Route::controller(OldResultController::class)->group(function () { // dashboard routes
    //     Route::get('/old_result', 'oldResult')->name('old_result');
    //     Route::post('/add-missing-result', 'addMissingResult')->name('add_missing_result');
    //     Route::post('/update-old-result', 'updateOldResult')->name('update_old_result');
    // });

     // Route::controller(PagesController::class)->group(function () { // dashboard routes
    //     Route::get('homepage', 'homepage')->name('homepage');
    //     Route::post('homepage/store', 'homepageStore')->name('homepage.store');
    //     Route::post('homepage/edit', 'homepageEdit')->name('homepage.edit');
    //     Route::post('homepage/update', 'homepageUpdate')->name('homepage.update');
    // });

    // Route::resource('setting', SettingController::class);

    // Route::resource('member', MemberController::class);
    // Route::post('/member/role_update', [MemberController::class, 'role_update'])->name('member.role_update');

    // Route::resource('role', RoleController::class);

    // Route::resource('faqs', FaqController::class);

    // Route::controller(WithdrawalController::class)->group(function () { // dashboard routes
    //     Route::group(['prefix' => 'withdrawal', 'as' => 'withdrawal.'], function () {
    //         Route::get('/{requests}', 'index')->name('requests');
    //         Route::get('/{approved}', 'index')->name('approved');
    //         Route::get('/{rejected}', 'index')->name('rejected');
    //         Route::get('/mark_approve/{id}', 'markApprove')->name('mark_approve');
    //         Route::get('/mark_reject/{id}', 'markReject')->name('mark_reject');
    //     });
    // });

    // Route::controller(GameHistoryController::class)->group(function () { // dashboard routes
    //     Route::get('/profit-loss-history', 'profitLossHistory')->name('profit_loss_history');
    //     Route::get('/bid-history', 'bidHistory')->name('bid_history');
    //     Route::post('/bid-users', 'bidUsers')->name('bid_users');
    // });

    // Route::controller(ProfileController::class)->group(function () { // dashboard routes
    //     Route::get('/profile', 'profile')->name('profile');
    //     Route::post('/update-profile', 'update')->name('update-profile');
    //     Route::post('/update-password', 'updatePassword')->name('update-password');
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
