<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function posts()
    {
        return $this->hasMany('\App\Models\Post');
    }

    public function comments()
    {
        return $this->hasMany('\App\Models\Comment');
    }

    public function votes()
    {
        return $this->hasMany('\App\Models\Vote');
    }

    public function totalLikePostsVotes()
    {
        return $this->HasManyThrough(
            Vote::class,
            Post::class,
            'user_id',
            'votable_id'
        )->where('votable_type', Post::class)
            ->where('state', 'like');
    }

    public function totalDislikePostsVotes()
    {
        return $this->HasManyThrough(
            Vote::class,
            Post::class,
            'user_id',
            'votable_id'
        )->where('votable_type', Post::class)
            ->where('state', 'dislike');
    }

    public function totalLikeCommentsVotes()
    {
        return $this->HasManyThrough(
            Vote::class,
            Comment::class,
            'user_id',
            'votable_id'
        )->where('votable_type', Comment::class)
            ->where('state', 'like');
    }

    public function totalDislikeCommentsVotes()
    {
        return $this->HasManyThrough(
            Vote::class,
            Comment::class,
            'user_id',
            'votable_id'
        )->where('votable_type', Comment::class)
            ->where('state', 'dislike');
    }
}
