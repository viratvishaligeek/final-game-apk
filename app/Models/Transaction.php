<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use BelongsToSite;
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'user_id',
        'amount',
        'balance',
        'subject',
        'type',
        'status',
        'game_id',
        'bid_id',
        'previous_balance',
        'admin_id',
        'idempotency_key',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'balance' => 'decimal:2',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
