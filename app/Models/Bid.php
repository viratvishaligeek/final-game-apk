<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bid extends Model
{
    use BelongsToSite;
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'order_no',
        'user_id',
        'game_id',
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

    public function winner()
    {
        return $this->hasOne(Winner::class);
    }
}
