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
    public function vote($voteableType, $voteableId, $state)
    {
        switch ($voteableType) {
            case 'Post':
                $voteable = Post::find($voteableId);
                break;
            case 'Comment':
                $voteable = Comment::find($voteableId);
                break;
        }
        if (!$voteable) {
            $response = [
                'message' => $voteableType . ' Not Found.'
            ];
            return response($response, Response::HTTP_NOT_FOUND);
        }
        //多態關聯
        $voted = $voteable->votes()->where('user_id', Auth::id())->first();
        if (!$voted) {
            $fields = [
                'user_id' => Auth::id(),
                'state' => $state
            ];
            $voteable->votes()->create($fields);
            $voted = $voteable->votes()->where('user_id', Auth::id())->first();
        } elseif ($voted->state != $state) {
            $fields = [
                'state' => $state
            ];
            $voted->update($fields);
            $voted = $voteable->votes()->where('user_id', Auth::id())->first();
        } elseif ($voted->state === $state) {
            $voted->delete();
            $voted = [
                'message' => 'cancelled vote'
            ];
        }

        $likeCount = $voteable->votes()->where('state','like')->count();
        $dislikeCount = $voteable->votes()->where('state','dislike')->count();
        $response = [
            'voted' => $voted,
            'like_count' => $likeCount,
            'dislike_count' => $dislikeCount
        ];

        return response($response, Response::HTTP_OK);
    }
}
