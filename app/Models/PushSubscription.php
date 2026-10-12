<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    use BelongsToSite;
    protected $fillable = ['user_id', 'admin_id', 'token', 'token_hash', 'platform', 'public_enabled'];

    protected function casts(): array
    {
        return ['public_enabled' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
