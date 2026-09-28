<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
}
