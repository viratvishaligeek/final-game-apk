<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GatewayWebhookSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_ignores_forged_success_and_uses_gateway_status_api(): void
    {
        $user = $this->createUser();
        $walletRequest = $this->createGatewayRequest($user);

        Http::fake([
            'https://api.ekqr.in/api/check_order_status' => Http::response([
                'status' => false,
                'msg' => 'Transaction not found',
            ], 200),
        ]);

        $response = $this->postJson('/api/v1/wallet/gateway/webhook', [
            'client_txn_id' => $walletRequest->client_txn_id,
            'status' => 'success',
            'amount' => '100.00',
        ]);

        $response->assertStatus(503);

        $this->assertEquals(0.0, (float) $user->fresh()->balance);
        $this->assertSame('processing', $walletRequest->fresh()->status);
        $this->assertSame(0, Transaction::query()->where('user_id', $user->id)->count());
    }

    public function test_verified_payment_is_credited_only_once_even_when_webhook_retries(): void
    {
        $user = $this->createUser();
        $walletRequest = $this->createGatewayRequest($user);

        Http::fake([
            'https://api.ekqr.in/api/check_order_status' => Http::response([
                'status' => true,
                'msg' => 'Transaction found',
                'data' => [
                    'id' => 12345,
                    'client_txn_id' => $walletRequest->client_txn_id,
                    'amount' => 100.00,
                    'status' => 'success',
                    'customer_vpa' => 'customer@upi',
                    'upi_txn_id' => 'UPI123456',
                ],
            ], 200),
        ]);

        $payload = [
            'client_txn_id' => $walletRequest->client_txn_id,
            'status' => 'success',
            'amount' => '100.00',
        ];

        $this->postJson('/api/v1/wallet/gateway/webhook', $payload)->assertOk();
        $this->postJson('/api/v1/wallet/gateway/webhook', $payload)->assertOk();

        $this->assertEquals(100.0, (float) $user->fresh()->balance);
        $this->assertSame('approved', $walletRequest->fresh()->status);
        $this->assertSame(1, Transaction::query()->where('user_id', $user->id)->count());
    }

    public function test_verified_amount_must_match_the_stored_order_amount(): void
    {
        $user = $this->createUser();
        $walletRequest = $this->createGatewayRequest($user);

        Http::fake([
            'https://api.ekqr.in/api/check_order_status' => Http::response([
                'status' => true,
                'msg' => 'Transaction found',
                'data' => [
                    'id' => 12345,
                    'client_txn_id' => $walletRequest->client_txn_id,
                    'amount' => 999.00,
                    'status' => 'success',
                ],
            ], 200),
        ]);

        $this->postJson('/api/v1/wallet/gateway/webhook', [
            'client_txn_id' => $walletRequest->client_txn_id,
            'status' => 'success',
        ])->assertStatus(503);

        $this->assertEquals(0.0, (float) $user->fresh()->balance);
        $this->assertSame('processing', $walletRequest->fresh()->status);
        $this->assertSame(0, Transaction::query()->where('user_id', $user->id)->count());
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'Gateway Test User',
            'phone' => '9876543210',
            'password' => 'test-password',
            'balance' => 0,
            'status' => 'active',
        ]);
    }

    private function createGatewayRequest(User $user): WalletRequest
    {
        return WalletRequest::create([
            'user_id' => $user->id,
            'request_type' => 'credit',
            'payment_method' => 'gateway',
            'amount' => 100,
            'status' => 'processing',
            'client_txn_id' => 'WLT-TEST-12345678',
            'remark' => 'Automated UPI gateway payment',
        ]);
    }
}
