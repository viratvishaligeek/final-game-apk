<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WalletRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiWalletRequestsRouteCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_wallet_requests_route_remains_available(): void
    {
        $user = User::create([
            'name' => 'Wallet API User',
            'phone' => '9876543210',
            'password' => 'Password123',
            'balance' => 100,
            'status' => 'active',
        ]);

        WalletRequest::create([
            'user_id' => $user->id,
            'request_type' => 'credit',
            'payment_method' => 'manual_upi',
            'amount' => 50,
            'status' => 'pending',
            'remark' => 'Manual top-up',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/wallet/requests')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.data.0.request_type', 'credit');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/wallet/get-money-request')
            ->assertOk()
            ->assertJsonPath('data.total', 1);
    }
}
