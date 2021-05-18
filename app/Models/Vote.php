<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo('\App\Model\User');
    }

    public function post()
    {
        return $this->hasOne('\App\Model\Post');
    }

    protected $fillable = ['post_id', 'state'];
}
