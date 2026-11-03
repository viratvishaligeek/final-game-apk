<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminPaymentAlert extends Model
{
    use BelongsToSite;

    protected $fillable = [
        'wallet_request_id',
        'attempt_id',
        'gateway_order_id',
        'error_category',
        'error_code',
        'http_status',
        'processing_stage',
        'diagnostic_reference',
        'severity',
        'fingerprint',
        'occurrence_count',
        'first_occurred_at',
        'latest_occurred_at',
        'user_id',
        'transaction_id',
        'amount',
        'gateway',
        'failure_reason',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'failed_at' => 'datetime',
            'first_occurred_at' => 'datetime',
            'latest_occurred_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AdminPaymentAlertRead::class);
    }
}
