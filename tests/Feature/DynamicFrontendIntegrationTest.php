<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\SupportChatController;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DynamicFrontendIntegrationTest extends TestCase
{
    public function test_support_chat_and_result_notification_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('admin.support-chat.index'));
        $this->assertTrue(Route::has('admin.results.notifications-toggle'));
        $this->assertNotNull(Route::getRoutes()->getByAction(SupportChatController::class . '@show'));
        $this->assertNotNull(Route::getRoutes()->getByAction(SupportChatController::class . '@send'));
    }

    public function test_support_chat_routes_require_the_expected_authentication_guards(): void
    {
        $userShow = Route::getRoutes()->getByAction(SupportChatController::class . '@show');
        $adminIndex = Route::getRoutes()->getByName('admin.support-chat.index');

        $this->assertContains('auth:sanctum', $userShow->gatherMiddleware());
        $this->assertContains('auth:admin', $adminIndex->gatherMiddleware());
    }
}
