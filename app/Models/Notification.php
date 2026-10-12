<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use BelongsToSite;
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

            // Fetch IDs first instead of using a same-table DELETE subquery, which
            // is rejected by some MySQL configurations (error 1093).
            $oldIds = static::query()
                ->where('user_id', $notification->user_id)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->skip(10)
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
