<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminPaymentAlert extends Model
{
    protected $fillable = [
        'wallet_request_id',
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
