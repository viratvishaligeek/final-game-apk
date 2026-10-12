<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\Notification;
use App\Models\User;
use App\Http\Middleware\ResolveSite;
use App\Http\Controllers\Api\SupportFaqController;
use App\Http\Controllers\Api\SupportChatController as ApiSupportChatController;
use App\Http\Controllers\Backend\SupportChatController as AdminSupportChatController;
use App\Http\Controllers\Backend\NotificationSoundController;
use App\Http\Controllers\Backend\AdminPushSubscriptionController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SupportFeaturesRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_notification_history_is_limited_to_ten_records_per_user(): void
    {
        $user = User::query()->create([
            'name' => 'Retention Test User',
            'phone' => '9999999912',
            'password' => 'test-password',
            'status' => 'active',
            'balance' => 0,
        ]);

        for ($index = 1; $index <= 12; $index++) {
            Notification::query()->create([
                'user_id' => $user->id,
                'subject' => 'test-' . $index,
                'message' => 'Notification ' . $index,
            ]);
        }

        $this->assertSame(10, Notification::query()->where('user_id', $user->id)->count());
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $user->id,
            'subject' => 'test-1',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'subject' => 'test-12',
        ]);
    }

    public function test_faq_endpoint_only_returns_faqs_for_selected_platform_and_shared_faqs(): void
    {
        Faq::query()->create(['question' => 'Web only', 'answer' => 'Web answer', 'platform' => 'web']);
        Faq::query()->create(['question' => 'App only', 'answer' => 'App answer', 'platform' => 'app']);
        Faq::query()->create(['question' => 'Shared', 'answer' => 'Shared answer', 'platform' => 'both']);

        $response = $this->getJson('/api/v1/faqs?platform=web');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['question' => 'Web only'])
            ->assertJsonFragment(['question' => 'Shared'])
            ->assertJsonMissing(['question' => 'App only']);
    }

    public function test_faq_queries_are_isolated_by_the_active_site(): void
    {
        Faq::withoutGlobalScope('site')->create([
            'question' => 'Khatauli-only test FAQ',
            'answer' => 'This must not leak across domains.',
            'platform' => 'both',
            'site_key' => 'play-online-khaiwal',
        ]);

        config(['sites.current_key' => 'galidisawar']);
        $response = app(SupportFaqController::class)->index(\Illuminate\Http\Request::create('/api/v1/faqs?platform=web'));

        $this->assertSame([], $response->getData(true)['data']);
    }

    public function test_admin_site_cookie_is_resolved_before_route_binding(): void
    {
        $request = \Illuminate\Http\Request::create(
            'https://playonlinekhaiwal.com/admin/dashboard',
            'GET',
            [],
            ['admin_site' => 'galidisawar']
        );

        app(ResolveSite::class)->handle($request, fn () => response()->noContent());

        $this->assertSame('galidisawar', config('sites.current_key'));
    }

    public function test_private_chat_media_routes_require_user_or_admin_authentication(): void
    {
        $userMedia = Route::getRoutes()->getByAction(ApiSupportChatController::class . '@media');
        $adminMedia = Route::getRoutes()->getByAction(AdminSupportChatController::class . '@media');

        $this->assertNotNull($userMedia);
        $this->assertContains('auth:sanctum', $userMedia->gatherMiddleware());
        $this->assertNotNull($adminMedia);
        $this->assertContains('auth:admin', $adminMedia->gatherMiddleware());
    }

    public function test_support_faq_and_sound_settings_routes_are_registered(): void
    {
        $faqRoute = Route::getRoutes()->getByAction(SupportFaqController::class . '@index');
        $soundPage = Route::getRoutes()->getByAction(NotificationSoundController::class . '@index');
        $soundUpdate = Route::getRoutes()->getByAction(NotificationSoundController::class . '@update');
        $adminPushSubscribe = Route::getRoutes()->getByAction(AdminPushSubscriptionController::class . '@subscribe');

        $this->assertNotNull($faqRoute);
        $this->assertContains('throttle:60,1', $faqRoute->gatherMiddleware());
        $this->assertNotNull($soundPage);
        $this->assertContains('auth:admin', $soundPage->gatherMiddleware());
        $this->assertNotNull($soundUpdate);
        $this->assertContains('auth:admin', $soundUpdate->gatherMiddleware());
        $this->assertNotNull($adminPushSubscribe);
        $this->assertContains('auth:admin', $adminPushSubscribe->gatherMiddleware());
    }
}
