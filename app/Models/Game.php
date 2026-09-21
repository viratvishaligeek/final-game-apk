<?php

namespace App\Models;

use App\Models\Result;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];

    public function Results()
    {
        return $this->hasMany(Result::class, 'game_id');
    }
}
