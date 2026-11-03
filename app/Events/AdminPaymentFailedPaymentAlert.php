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
            'transaction_id' => $alert->transaction_id,
            'amount' => (float) $alert->amount,
            'gateway' => $alert->gateway,
            'failure_reason' => $alert->failure_reason,
            'failed_at' => $alert->failed_at?->toIso8601String(),
        ];
    }
}
