<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Comment;
use App\Models\Vote;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content'];

//    protected $casts = [
//        'created_at' => 'datetime:Y-m-d H:i:s',
//        'updated_at' => 'datetime:Y-m-d H:i:s'
//    ];

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
        return $this->morphMany('\App\Models\Vote', 'votable');
    }

    public function images()
    {
        return $this->morphMany('\App\Models\Image', 'imageable');
    }

    public function commentsVotes()
    {
        return $this->hasManyThrough(
            Vote::class,
            Comment::class,
            'post_id',
            'votable_id'
        )->where('votable_type', Comment::class);
    }

}
