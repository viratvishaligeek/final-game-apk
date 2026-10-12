<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;

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
        // Keep a user's private notification history bounded regardless of which
        // controller/service creates the record. Public broadcasts (user_id=null)
        // are shared announcements and are intentionally not deleted here.
        static::created(function (Notification $notification): void {
            if ($notification->user_id === null) {
                return;
            }

            $latestIds = static::query()
                ->where('user_id', $notification->user_id)
                ->latest('created_at')
                ->latest('id')
                ->limit(10)
                ->select('id');

            static::query()
                ->where('user_id', $notification->user_id)
                ->whereNotIn('id', $latestIds)
                ->delete();
        });
    }
}
