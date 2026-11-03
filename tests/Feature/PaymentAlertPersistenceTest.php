<?php

namespace Tests\Feature;

use App\Events\AdminPaymentFailedPaymentAlert;
use App\Models\User;
use App\Models\WalletRequest;
use App\Services\PaymentAlertRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PaymentAlertPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_technical_gateway_error_is_persisted_without_a_gateway_transaction_id_and_repeated_callbacks_are_correlated(): void
    {
        $user = User::create([
            'name' => 'Payment Alert Test',
            'phone' => '919999' . random_int(100000, 999999),
            'password' => 'test-password',
            'status' => 'active',
        ]);

        $request = WalletRequest::create([
            'user_id' => $user->id,
            'request_type' => 'credit',
            'payment_method' => 'gateway',
            'amount' => 250,
            'status' => 'failed',
            'client_txn_id' => 'PAYTEST-' . bin2hex(random_bytes(8)),
            'gateway_status' => 'order_creation_failed',
        ]);

        $recorder = app(PaymentAlertRecorder::class);
        $context = [
            'stage' => 'order_creation',
            'message' => 'Gateway order creation failed after an upstream timeout.',
            'error_code' => 'UPSTREAM_TIMEOUT',
            'http_status' => 504,
        ];

        $first = $recorder->record($request, $context);
        $second = $recorder->record($request, $context);

        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertSame($first->id, $second->id);
        $this->assertSame('PAYMENT_GATEWAY_API_ERROR', $second->error_category);
        $this->assertSame($request->client_txn_id, $second->attempt_id);
        $this->assertNull($second->transaction_id);
        $this->assertSame(2, (int) $second->occurrence_count);
        $this->assertSame(1, $second->newQuery()->count());

    }

    public function test_exact_active_plan_message_is_classified_as_gateway_configuration_error(): void
    {
        Event::fake([AdminPaymentFailedPaymentAlert::class]);

        $user = User::create([
            'name' => 'Plan Error Test',
            'phone' => '918888' . random_int(100000, 999999),
            'password' => 'test-password',
            'status' => 'active',
        ]);

        $request = WalletRequest::create([
            'user_id' => $user->id,
            'request_type' => 'credit',
            'payment_method' => 'gateway',
            'amount' => 100,
            'status' => 'failed',
            'client_txn_id' => 'PLANTEST-' . bin2hex(random_bytes(8)),
        ]);

        $alert = app(PaymentAlertRecorder::class)->record($request, [
            'stage' => 'order_creation',
            'message' => 'Active Plan not found. Please Buy Plan',
        ]);

        $this->assertSame('PAYMENT_GATEWAY_CONFIGURATION_ERROR', $alert?->error_category);
        $this->assertStringContainsString('no active plan', mb_strtolower((string) $alert?->failure_reason));
        $this->assertSame('critical', $alert?->severity);
    }
}
