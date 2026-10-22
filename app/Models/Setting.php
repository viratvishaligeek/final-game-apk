<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use BelongsToSite;
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
}
