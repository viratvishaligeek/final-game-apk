<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'user_id',
        'phone',
        'subject',
        'message',
    ];

    protected static function booted(): void
    {
        static::created(function (Notification $notification): void {
            if ($notification->user_id === null) {
                return;
            }

            $oldIds = static::query()
                ->where('user_id', $notification->user_id)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(1000000)
                ->offset(10)
                ->pluck('id');

            if ($oldIds->isNotEmpty()) {
                static::query()
                    ->where('user_id', $notification->user_id)
                    ->whereIn('id', $oldIds)
                    ->delete();
            }
        });
    }
}
