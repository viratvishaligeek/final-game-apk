<?php

use App\Http\Controllers\Backend\AdminPaymentAlertController;
use App\Http\Controllers\Backend\AdminPushSubscriptionController;
use App\Http\Controllers\Backend\Auth\AuthController;
use App\Http\Controllers\Backend\BannerController;
use App\Http\Controllers\Backend\CommunityModerationController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\FaqController;
use App\Http\Controllers\Backend\Game\BiddingDeskController;
use App\Http\Controllers\Backend\Game\GameController;
use App\Http\Controllers\Backend\Game\ResultController;
use App\Http\Controllers\Backend\Game\WinnerController;
use App\Http\Controllers\Backend\HomePageController;
use App\Http\Controllers\Backend\MobileAppController;
use App\Http\Controllers\Backend\PagesController;
use App\Http\Controllers\Backend\PushNotificationController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\SupportChatController;
use App\Http\Controllers\Backend\User\MemberController;
use App\Http\Controllers\Backend\User\RoleController;
use App\Http\Controllers\Backend\User\UserController;
use App\Http\Controllers\Backend\User\WalletController;
use App\Http\Controllers\Backend\User\WalletRequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin authentication
|--------------------------------------------------------------------------
*/
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::post('/try-login', 'tryLogin')->name('try_login');
    Route::get('/forgot_password', 'forgetPassword')->name('forgot_password');
    Route::get('/recoverPassword', 'velidateEmail')->name('recoverPassword');
});

/*
|--------------------------------------------------------------------------
| Authenticated Admin workspace
|--------------------------------------------------------------------------
| Keep the existing URI and route-name contracts stable. All admin workspace
| endpoints remain protected by the admin guard and active-site middleware.
*/
Route::prefix('admin')->as('admin.')->middleware(['auth:admin', 'admin.site'])->group(function () {
    // Dashboard and account settings.
    Route::controller(DashboardController::class)->group(function () {
        Route::get('dashboard', 'dashboard')->name('dashboard');
        Route::get('profile', 'profile')->name('profile');
        Route::post('update-profile', 'update')->name('update-profile');
        Route::post('update-password', 'updatePassword')->name('update-password');
        Route::get('logout', 'logout')->name('logout');
    });

    // Site-scoped payment alerts.
    Route::get('payment-alerts/pending', [AdminPaymentAlertController::class, 'pending'])->name('payment-alerts.pending');
    Route::post('payment-alerts/{alert}/acknowledge', [AdminPaymentAlertController::class, 'acknowledge'])->name('payment-alerts.acknowledge');

    // Support conversations, search, media, and message actions.
    Route::prefix('support-chat')->as('support-chat.')->controller(SupportChatController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('conversations', 'conversations')->name('conversations');
        Route::get('users', 'searchUsers')->name('users');
        Route::post('conversations', 'startConversation')->name('start');
        Route::get('media/{message}', 'media')->name('media');
        Route::get('{conversation}/messages', 'messages')->name('messages');
        Route::post('{conversation}/send', 'send')->name('send');
    });

    // Push subscriptions and Admin-issued notifications.
    Route::post('push-subscribe', [AdminPushSubscriptionController::class, 'subscribe'])->name('push-subscribe');
    Route::delete('push-subscribe', [AdminPushSubscriptionController::class, 'unsubscribe'])->name('push-unsubscribe');
    Route::get('push-notifications', [PushNotificationController::class, 'index'])->name('push-notifications.index');
    Route::post('push-notifications/broadcast', [PushNotificationController::class, 'broadcast'])->name('push-notifications.broadcast');

    // Community moderation.
    Route::prefix('guessing')->as('guessing.')->controller(CommunityModerationController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::patch('{post}/moderate', 'update')->name('moderate');
        Route::post('policy', 'updatePolicy')->name('policy');
    });

    // Game catalogue and result operations.
    Route::resource('games', GameController::class);
    Route::controller(ResultController::class)->prefix('results')->name('results.')->group(function () {
        Route::get('/results', 'index')->name('index');
        Route::post('/results/save', 'storeOrUpdate')->name('storeOrUpdate');
        Route::post('/results/revert', 'revert')->name('revert');
        Route::post('/notifications-toggle', 'updateNotifications')->name('notifications-toggle');
    });
    Route::resource('winner', WinnerController::class);
    Route::controller(BiddingDeskController::class)->prefix('bidding-desk')->name('bidding-desk.')->group(function () {
        Route::get('/index', 'index')->name('index');
        Route::get('/data', 'data')->name('data');
        Route::get('/details', 'details')->name('details');
        Route::get('/profit-loss', 'profitLoss')->name('profit_loss');
        Route::get('/profit-loss/transactions', 'profitLossTransactions')->name('profit_loss_transactions');
    });

    // User accounts, roles, and staff members.
    Route::get('users/{user}/bids', [UserController::class, 'bids'])->name('users.bids');
    Route::resource('users', UserController::class);
    Route::patch('users/toggle-status/{id}', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::resource('member', MemberController::class);
    Route::resource('roles', RoleController::class);

    // Wallet requests and manual balance adjustments.
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::controller(WalletController::class)->group(function () {
            Route::post('debit/{id}', 'debit')->name('debit');
            Route::post('credit/{id}', 'credit')->name('credit');
        });
        Route::controller(WalletRequestController::class)->group(function () {
            Route::get('request-add', 'addRequests')->name('request-add');
            Route::get('request-withdraw', 'withdrawRequests')->name('request-withdraw');
            Route::get('request/{walletRequest}', 'show')->name('request-show');
            Route::post('request/{walletRequest}/approve', 'approve')->name('request.approve');
            Route::post('request/{walletRequest}/reject', 'reject')->name('request.reject');
        });
    });

    // CMS and content administration.
    Route::resource('pages', PagesController::class);
    Route::resource('home-page', HomePageController::class);
    Route::resource('faqs', FaqController::class);
    Route::resource('banner', BannerController::class);
    Route::resource('setting', SettingController::class);

    // Mobile application configuration.
    Route::prefix('mobile-app')->name('mobile-app.')->controller(MobileAppController::class)->group(function () {
        Route::get('marque', 'marque')->name('marque');
        Route::post('marque', 'updateMarque')->name('update_marque');
        Route::get('limits', 'limits')->name('limits');
        Route::post('limits', 'updateLimits')->name('update_limits');
        Route::get('notice', 'notice')->name('notice');
        Route::post('notice', 'updateNotice')->name('update_notice');
    });
});
