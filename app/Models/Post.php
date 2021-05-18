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

    public function vote()
    {
        return $this->hasOne('\App\Model\Post');
    }
//    public function resolveRouteBinding($value, $field = null)
//    {
//        return $this->where('id', $value)->firstOrFail();
//    }

    protected $fillable = ['title', 'content','image'];
}
