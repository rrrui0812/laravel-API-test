<?php

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class User extends JsonResource
{
    public static $wrap = 'user';

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
//        return parent::toArray($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'posts_count' => $this->when(isset($this->posts_count), $this->posts_count),
            'comments_count' => $this->when(isset($this->comments_count), $this->comments_count),
            'total_like_votes_count' => $this->when(
                isset($this->total_like_posts_votes_count) && isset($this->total_like_comments_votes_count),
                $this->total_like_posts_votes_count + $this->total_like_comments_votes_count
            ),
            'total_dislike_votes_count' => $this->when(
                isset($this->total_dislike_posts_votes_count) && isset($this->total_dislike_comments_votes_count),
                $this->total_dislike_posts_votes_count + $this->total_dislike_comments_votes_count
            ),

//            'total_like_votes_count' => $this->merge([
//                'total_like_votes_count' => $this->total_like_posts_votes_count + $this->total_like_comments_votes_count,
//                'total_like_posts_votes_count' => $this->total_like_posts_votes_count,
//                'total_like_comments_votes_count' => $this->total_like_comments_votes_count,
//            ]),
//            'total_dislike_votes_count' => $this->merge([
//                'total_dislike_votes_count' => $this->total_dislike_posts_votes_count + $this->total_dislike_comments_votes_count,
//                'total_dislike_posts_votes_count' => $this->total_dislike_posts_votes_count,
//                'total_dislike_comments_votes_count' => $this->total_dislike_comments_votes_count,
//            ]),
            'posts' => PostCollection::make($this->whenLoaded('posts')),
        ];
    }
}
