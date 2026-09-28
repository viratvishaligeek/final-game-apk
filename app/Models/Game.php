<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Game extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'slug',
        'result_time',
        'play_start',
        'play_end',
        'last_result',
        'status',
        'serial',
    ];

    protected $casts = [
        'serial' => 'integer',
    ];

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function getIsPlayableAttribute(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        $now = now();
        $start = now()->setTimeFromTimeString($this->play_start);
        $end = now()->setTimeFromTimeString($this->play_end);
        if ($end->lessThanOrEqualTo($start)) {
            return $now->greaterThanOrEqualTo($start)
                || $now->lessThanOrEqualTo($end);
        }
        return $now->betweenIncluded($start, $end);
    }
}
