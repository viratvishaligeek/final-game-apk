<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralReward extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'first_deposit_amount' => 'decimal:2',
        'reward_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function walletRequest()
    {
        return $this->belongsTo(WalletRequest::class, 'first_wallet_request_id');
    }
}
