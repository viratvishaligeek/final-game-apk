<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomePage extends Model
{
    use BelongsToSite;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'status',
        'location',
        'whatsapp',
        'phone',
        'telegram',
        'background',
        'image',
        'image_alt',
        'button_label',
        'button_url',
        'button_style',
        'layout',
        'display_order',
    ];
}
