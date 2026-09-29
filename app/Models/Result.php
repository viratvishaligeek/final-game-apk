<?php

namespace App\Models;

use App\Models\GameResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Result extends Model
{
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $casts = [
        'number_date' => 'date',
    ];
    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
