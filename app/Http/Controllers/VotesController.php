<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Vote;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class VotesController extends Controller
{
    public function vote($votableType, $votableId, $state)
    {
        switch ($votableType) {
            case 'Post':
                $votable = Post::find($votableId);
                break;
            case 'Comment':
                $votable = Comment::find($votableId);
                break;
        }
        if (!$votable) {
            $response = [
                'message' => $votableType . ' Not Found.'
            ];
            return response($response, Response::HTTP_NOT_FOUND);
        }
        //多態關聯
        $voted = $votable->votes()->where('user_id', Auth::id())->first();
        if (!$voted) {
            $fields = [
                'user_id' => Auth::id(),
                'state' => $state
            ];
            $votable->votes()->create($fields);
            $voted = $votable->votes()->where('user_id', Auth::id())->first();
        } elseif ($voted->state != $state) {
            $fields = [
                'state' => $state
            ];
            $voted->update($fields);
            $voted = $votable->votes()->where('user_id', Auth::id())->first();
        } elseif ($voted->state === $state) {
            $voted->delete();
            $voted = [
                'message' => 'cancelled vote'
            ];
        }

        $likeCount = $votable->votes()->where('state','like')->count();
        $dislikeCount = $votable->votes()->where('state','dislike')->count();
        $response = [
            'voted' => $voted,
            'like_count' => $likeCount,
            'dislike_count' => $dislikeCount
        ];

        return response($response, Response::HTTP_OK);
    }
}
