<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class UpiGatewayPaymentRoutesTest extends TestCase
{
    public function test_gateway_status_route_is_authenticated_and_uses_wallet_controller(): void
    {
        $route = collect(Route::getRoutes()->getRoutes())->first(
            fn ($candidate) => str_ends_with($candidate->uri(), 'wallet/gateway/{requestId}/status')
        );

        $this->assertNotNull($route);
        $this->assertStringContainsString(
            WalletController::class . '@gatewayStatus',
            $route->getActionName()
        );
        $this->assertContains('auth:sanctum', $route->gatherMiddleware());
        $this->assertContains('throttle:30,1', $route->gatherMiddleware());
    }

    public function test_gateway_status_endpoint_does_not_allow_anonymous_access(): void
    {
        $this->getJson('/api/v1/wallet/gateway/1/status')
            ->assertUnauthorized();
    }
}
