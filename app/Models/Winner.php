<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Winner extends Model
{
    protected $fillable = [
        'bid_id',
        'user_id',
        'game_id',
        'number',
        'type',
        'game_date',
        'amount',
        'winning_amount',
    ];

    protected $casts = [
        'game_date' => 'date',
        'amount' => 'decimal:2',
        'winning_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function bid(): BelongsTo
    {
        return $this->belongsTo(Bid::class);
    }
}
