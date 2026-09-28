<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'phone',
        'subject',
        'message',
    ];
}
