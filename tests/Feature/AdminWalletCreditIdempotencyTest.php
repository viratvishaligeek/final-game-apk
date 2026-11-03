<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\Admin\AdminWalletCreditService;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class AdminWalletCreditIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_credit_service_marks_replays_and_rejects_key_reuse_for_another_amount(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'balance' => 500.00,
        ]);

        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('create')->once();
        $walletService = new WalletService($notificationService);
        $creditService = new AdminWalletCreditService($walletService);
        $key = (string) Str::uuid();

        $first = $creditService->credit($user, 200.00, 'Admin credit test', $key, null);
        $replay = $creditService->credit($user, 200.00, 'Admin credit test', $key, null);

        $this->assertFalse($first['duplicate']);
        $this->assertTrue($replay['duplicate']);
        $this->assertSame($first['transaction']->id, $replay['transaction']->id);
        $this->assertEquals(700.00, (float) $user->fresh()->balance);
        $this->assertSame(1, Transaction::query()->where('idempotency_key', $key)->count());

        $this->expectException(ConflictHttpException::class);
        $creditService->credit($user, 300.00, 'Different amount', $key, null);
    }

    public function test_admin_credit_key_cannot_be_replayed_against_a_different_user(): void
    {
        $owner = User::factory()->create([
            'status' => 'active',
            'balance' => 500.00,
        ]);
        $other = User::factory()->create([
            'status' => 'active',
            'balance' => 300.00,
        ]);

        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('create')->once();
        $walletService = new WalletService($notificationService);
        $creditService = new AdminWalletCreditService($walletService);
        $key = (string) Str::uuid();

        $creditService->credit($owner, 100.00, 'First user', $key, null);

        $this->expectException(ConflictHttpException::class);
        $creditService->credit($other, 100.00, 'Second user', $key, null);
    }

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
