<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo('\App\Models\User');
    }

    public function comments()
    {
        return $this->hasMany('\App\Models\Comment');
    }

    public function votes()
    {
        return $this->hasMany('\App\Models\Vote');
    }
//    public function resolveRouteBinding($value, $field = null)
//    {
//        return $this->where('id', $value)->firstOrFail();
//    }

    protected $fillable = ['title', 'content', 'image'];
}
