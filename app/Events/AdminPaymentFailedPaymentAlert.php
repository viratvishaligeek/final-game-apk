<?php

namespace App\Events;

use App\Models\AdminPaymentAlert;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminPaymentFailedPaymentAlert implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public int $alertId) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin.payment-alerts')];
    }

    public function broadcastAs(): string
    {
        return 'payment.failed';
    }

    public function broadcastWith(): array
    {
        $alert = AdminPaymentAlert::query()
            ->with('user:id,name,phone')
            ->find($this->alertId);

        if (!$alert) {
            return [];
        }

        return [
            'id' => $alert->id,
            'user_name' => $alert->user?->name ?? 'Unknown user',
            'user_id' => $alert->user_id,
            'phone' => $alert->user?->phone,
            'attempt_id' => $alert->attempt_id,
            'transaction_id' => $alert->transaction_id,
            'gateway_order_id' => $alert->gateway_order_id,
            'amount' => (float) $alert->amount,
            'gateway' => $alert->gateway,
            'failure_reason' => $alert->failure_reason,
            'error_category' => $alert->error_category,
            'error_code' => $alert->error_code,
            'http_status' => $alert->http_status,
            'processing_stage' => $alert->processing_stage,
            'diagnostic_reference' => $alert->diagnostic_reference,
            'severity' => $alert->severity,
            'occurrence_count' => $alert->occurrence_count,
            'first_occurred_at' => $alert->first_occurred_at?->toIso8601String(),
            'latest_occurred_at' => $alert->latest_occurred_at?->toIso8601String(),
            'failed_at' => $alert->failed_at?->toIso8601String(),
        ];
    }
}
