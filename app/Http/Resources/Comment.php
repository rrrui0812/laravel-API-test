<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Comment extends JsonResource
{
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
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'like_count' => $this->when(isset($this->like_comment_votes_count), $this->like_comment_votes_count),
            'dislike_count' => $this->when(isset($this->dislike_comment_votes_count), $this->dislike_comment_votes_count),
            'user' => User::make($this->whenLoaded('user')),
        ];
    }
}
