<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bid extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'order_no',
        'user_id',
        'game_id',
        'phone',
        'game_date',
        'type',
        'number',
        'amount',
        'status',
        'winning_amount',
    ];

    protected $casts = [
        'game_date' => 'date',
        'amount' => 'decimal:2',
        'winning_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
