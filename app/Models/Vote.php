<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo('\App\Models\User');
    }

    public function post()
    {
        return $this->belongsTo('\App\Models\Post');
    }

    public function comment()
    {
        return $this->belongsTo('\App\Models\Comment');
    }

    public function votable()
    {
        return $this->morphTo();
    }

    protected $fillable = ['votable_type', 'votable_id', 'state', 'user_id'];
}
