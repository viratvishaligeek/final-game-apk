<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WalletRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletRequestIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_withdrawals_cannot_overcommit_balance_across_pending_requests(): void
    {
        $user = $this->createUser(100);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/wallet/withdraw', [
                'amount' => '80.00',
                'mode' => 'upi',
                'upi_id' => 'member@upi',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/wallet/withdraw', [
                'amount' => '30.00',
                'mode' => 'upi',
                'upi_id' => 'member@upi',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('amount');

        $this->assertEquals(100.0, (float) $user->fresh()->balance);
        $this->assertSame(
            1,
            WalletRequest::query()
                ->where('user_id', $user->id)
                ->where('request_type', 'debit')
                ->count()
        );
    }

    public function test_wallet_request_list_returns_all_owned_requests_when_filters_are_omitted(): void
    {
        $user = $this->createUser(500);

        WalletRequest::create([
            'user_id' => $user->id,
            'request_type' => 'credit',
            'payment_method' => 'manual_upi',
            'amount' => 100,
            'status' => 'pending',
            'utr' => 'UTR-TEST-001',
            'remark' => 'Manual top-up',
        ]);

        WalletRequest::create([
            'user_id' => $user->id,
            'request_type' => 'debit',
            'payment_method' => 'upi',
            'amount' => 50,
            'status' => 'approved',
            'upi_id' => 'member@upi',
            'remark' => 'Withdrawal',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/wallet/get-money-request')
            ->assertOk()
            ->assertJsonCount(2, 'data.data');
    }

    public function test_wallet_request_list_rejects_invalid_filters(): void
    {
        $user = $this->createUser(100);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/wallet/get-money-request?status=anything')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    private function createUser(float $balance): User
    {
        return User::create([
            'name' => 'Wallet Test User',
            'phone' => '9876543210',
            'password' => 'test-password',
            'balance' => $balance,
            'status' => 'active',
        ]);
    }
}
