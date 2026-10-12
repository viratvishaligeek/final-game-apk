<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\SupportChatController as ApiSupportChatController;
use App\Http\Controllers\Backend\SupportChatController as AdminSupportChatController;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DynamicFrontendIntegrationTest extends TestCase
{
    public function test_support_chat_and_result_notification_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('admin.support-chat.index'));
        $this->assertTrue(Route::has('admin.results.notifications-toggle'));
        $this->assertTrue(method_exists(ApiSupportChatController::class, 'show'));
        $this->assertTrue(method_exists(ApiSupportChatController::class, 'send'));
        $this->assertTrue(method_exists(AdminSupportChatController::class, 'conversations'));
        $this->assertTrue(method_exists(AdminSupportChatController::class, 'send'));
    }

    public function test_admin_support_chat_route_is_guarded(): void
    {
        $adminIndex = Route::getRoutes()->getByName('admin.support-chat.index');
        $this->assertNotNull($adminIndex);
        $this->assertContains('auth:admin', $adminIndex->middleware());
    }
}
