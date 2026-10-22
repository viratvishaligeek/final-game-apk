<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use App\Services\ProjectStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use BelongsToSite;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'image',
        'status',
    ];
    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? app(ProjectStorage::class)->publicUrl($this->image)
            : asset('backend/assets/img/default-banner.png');
    }

    // Helper attribute for image URL
}
