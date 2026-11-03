<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPresenceSession extends Model
{
    use BelongsToSite;

    protected $fillable = ['user_id', 'session_id', 'platform', 'app_state', 'last_seen_at', 'ended_at'];

    protected function casts(): array
    {
        return ['last_seen_at' => 'datetime', 'ended_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
