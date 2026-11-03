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
        $alert = AdminPaymentAlert::query()->find($this->alertId);

        return $alert
            ? [new PrivateChannel('admin.payment-alerts.' . $alert->site_key)]
            : [];
    }

    public function broadcastAs(): string
    {
        return 'payment.failed';
    }

    public function broadcastWith(): array
    {
        $alert = AdminPaymentAlert::query()
            ->with('user:id,name,phone')
            ->addSelect([
                'related_occurrences' => AdminPaymentAlert::withoutGlobalScopes()->from('admin_payment_alerts as related_alerts')->selectRaw('COUNT(*)')->where('related_alerts.site_key', config('sites.current_key', config('sites.default', 'play-online-khaiwal')))->whereNotNull('related_alerts.fingerprint')->whereColumn('related_alerts.fingerprint', 'admin_payment_alerts.fingerprint'),
                'related_first_occurred_at' => AdminPaymentAlert::withoutGlobalScopes()->from('admin_payment_alerts as related_alerts')->selectRaw('MIN(related_alerts.first_occurred_at)')->where('related_alerts.site_key', config('sites.current_key', config('sites.default', 'play-online-khaiwal')))->whereNotNull('related_alerts.fingerprint')->whereColumn('related_alerts.fingerprint', 'admin_payment_alerts.fingerprint'),
                'related_latest_occurred_at' => AdminPaymentAlert::withoutGlobalScopes()->from('admin_payment_alerts as related_alerts')->selectRaw('MAX(related_alerts.latest_occurred_at)')->where('related_alerts.site_key', config('sites.current_key', config('sites.default', 'play-online-khaiwal')))->whereNotNull('related_alerts.fingerprint')->whereColumn('related_alerts.fingerprint', 'admin_payment_alerts.fingerprint'),
            ])
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
            'fingerprint' => $alert->fingerprint,
            'occurrence_count' => $alert->occurrence_count,
            'related_occurrences' => $alert->related_occurrences,
            'related_first_occurred_at' => $alert->related_first_occurred_at,
            'related_latest_occurred_at' => $alert->related_latest_occurred_at,
            'first_occurred_at' => $alert->first_occurred_at?->toIso8601String(),
            'latest_occurred_at' => $alert->latest_occurred_at?->toIso8601String(),
            'failed_at' => $alert->failed_at?->toIso8601String(),
        ];
    }
}
