<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityPost extends Model
{
    use BelongsToSite;
    protected $fillable = [
        'game_id', 'user_id', 'title', 'body', 'status', 'moderated_by', 'moderated_at',
    ];

    protected function casts(): array
    {
        return ['moderated_at' => 'datetime'];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class)->withTrashed();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function replies(): HasMany
    {
        return $this->hasMany(CommunityReply::class, 'community_post_id')->latest();
    }
}
