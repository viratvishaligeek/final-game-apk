<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'phone',
        'password',
        'gender',
        'city',
        'address',
        'balance',
        'bank_name',
        'account_number',
        'ifsc_code',
        'account_holder_name',
        'phonepe',
        'gpay',
        'paytm',
        'status',
    ];


    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function walletRequests()
    {
        return $this->hasMany(WalletRequest::class);
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function completedTransactions()
    {
        return $this->transactions()
            ->where('status', 'completed');
    }

    public function getWalletCreditAttribute()
    {
        return $this->completedTransactions()
            ->where('type', 'credit')
            ->sum('amount');
    }

    public function getWalletDebitAttribute()
    {
        return $this->completedTransactions()
            ->where('type', 'debit')
            ->sum('amount');
    }

    public function getWalletBalanceAttribute()
    {
        return $this->wallet_credit - $this->wallet_debit;
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    protected $hidden = [
        'password',
    ];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
