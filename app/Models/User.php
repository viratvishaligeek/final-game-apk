<?php

namespace App\Models;

use App\Models\GameResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];

}
