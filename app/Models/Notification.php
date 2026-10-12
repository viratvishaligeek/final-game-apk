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
        // Bound private notification history regardless of which code path creates it.
        // Shared public announcements (user_id=null) are not deleted per-user.
        static::created(function (Notification $notification): void {
            if ($notification->user_id === null) {
                return;
            }

            $latestIds = static::query()
                ->where('user_id', $notification->user_id)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(10)
                ->select('id');

            static::query()
                ->where('user_id', $notification->user_id)
                ->whereNotIn('id', $latestIds)
                ->delete();
        });
    }
}
