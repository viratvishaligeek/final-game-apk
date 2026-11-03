<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminPaymentAlertRead extends Model
{
    use BelongsToSite;

    protected $fillable = ['admin_payment_alert_id', 'admin_id', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function alert(): BelongsTo
    {
        return $this->belongsTo(AdminPaymentAlert::class, 'admin_payment_alert_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
