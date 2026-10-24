<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class AdminWalletCreditIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeating_the_same_credit_key_does_not_credit_the_wallet_twice(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'balance' => 500.00,
        ]);

        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('create')->once();

        $walletService = new WalletService($notificationService);
        $key = (string) Str::uuid();

        $first = $walletService->credit($user, 200.00, 'Admin credit test', [
            'admin_id' => null,
            'idempotency_key' => $key,
        ]);

        $second = $walletService->credit($user, 200.00, 'Admin credit test', [
            'admin_id' => null,
            'idempotency_key' => $key,
        ]);

        $this->assertSame($first->id, $second->id);
        $this->assertEquals(700.00, (float) $user->fresh()->balance);
        $this->assertEquals(500.00, (float) $first->previous_balance);
        $this->assertEquals(700.00, (float) $first->balance);
        $this->assertSame(1, Transaction::query()->where('idempotency_key', $key)->count());
    }
}
