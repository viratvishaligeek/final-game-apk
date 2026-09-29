<?php

namespace App\Models;

use Carbon\Carbon;
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
    protected $appends = [
        'is_playable',
    ];

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function latestResult()
    {
        return $this->hasOne(Result::class)
            ->latestOfMany();
    }

    public function getIsPlayableAttribute(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        if (!$this->play_start || !$this->play_end) {
            return false;
        }
        $now = now();
        $startTime = Carbon::createFromFormat(
            'H:i:s',
            $this->play_start
        );
        $endTime = Carbon::createFromFormat(
            'H:i:s',
            $this->play_end
        );

        if ($endTime->greaterThan($startTime)) {
            $start = $now->copy()->setTime($startTime->hour, $startTime->minute, $startTime->second);
            $end = $now->copy()->setTime($endTime->hour, $endTime->minute, $endTime->second);

            return $now->greaterThanOrEqualTo($start)
                && $now->lessThan($end);
        }

        $startToday = $now->copy()->setTime($startTime->hour, $startTime->minute, $startTime->second);

        $endToday = $now->copy()->setTime($endTime->hour, $endTime->minute, $endTime->second);

        if ($now->greaterThanOrEqualTo($startToday)) {
            return true;
        }
        return $now->lessThan($endToday);
    }
}
