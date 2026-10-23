<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

            // Serialize cleanup per user. The user-row lock coordinates concurrent
            // notification inserts without blocking notifications for other users.
            DB::transaction(function () use ($notification): void {
                User::query()
                    ->whereKey($notification->user_id)
                    ->lockForUpdate()
                    ->first();

                $oldIds = static::query()
                    ->where('user_id', $notification->user_id)
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->offset(10)
                    ->limit(1000000)
                    ->pluck('id');

                if ($oldIds->isNotEmpty()) {
                    static::query()
                        ->where('user_id', $notification->user_id)
                        ->whereIn('id', $oldIds)
                        ->delete();
                }
            }, 3);
        });
    }
}
