<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CommentCollection;
use Illuminate\Support\Facades\Auth;

class Post extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public $preserveKeys = true;

//    public static $wrap = 'resource';

    public function toArray($request)
    {
//        return parent::toArray($request);
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'comments_count' => $this->when(isset($this->comments_count), $this->comments_count),
            'like_count' => $this->when(isset($this->like_post_votes_count), $this->like_post_votes_count),
            'dislike_count' => $this->when(isset($this->dislike_post_votes_count), $this->dislike_post_votes_count),
            'images' => ImageCollection::make($this->whenLoaded('images')),
            'user' => User::make($this->whenLoaded('user')),
            'comments' => CommentCollection::make($this->whenLoaded('comments')),
        ];
    }
}
