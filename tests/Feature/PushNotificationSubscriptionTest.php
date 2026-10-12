<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\PushSubscriptionController;
use App\Services\WalletService;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;
use Tests\TestCase;

class PushNotificationSubscriptionTest extends TestCase
{
    public function test_push_subscription_routes_keep_their_public_and_user_access_contracts(): void
    {
        $publicSubscribe = Route::getRoutes()->getByAction(PushSubscriptionController::class . '@subscribePublic');
        $userSubscribe = Route::getRoutes()->getByAction(PushSubscriptionController::class . '@subscribeUser');

        $this->assertNotNull($publicSubscribe);
        $this->assertNotNull($userSubscribe);
        $this->assertContains('throttle:20,1', $publicSubscribe->gatherMiddleware());
        $this->assertContains('auth:sanctum', $userSubscribe->gatherMiddleware());
    }

    public function test_wallet_debits_still_notify_by_default_but_can_suppress_bid_notifications(): void
    {
        $method = new ReflectionMethod(WalletService::class, 'debit');
        $parameter = $method->getParameters()[3] ?? null;

        $this->assertNotNull($parameter);
        $this->assertSame('notifyUser', $parameter->getName());
        $this->assertTrue($parameter->getDefaultValue());
    }
}
