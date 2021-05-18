<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Vote;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class VotesController extends Controller
{
    public function vote($postId, $vote)
    {
        $voted = Vote::where('user_id', Auth::id())->where('post_id', $postId)->first();
        if (is_null($voted)) {
            $content = [
                'post_id' => $postId,
                'state' => $vote
            ];
            $voted = auth()->user()->votes()->create($content);
        } elseif ($voted->state != $vote) {
            $content = [
                'post_id' => $postId,
                'state' => $vote
            ];
            $voted->update($content);
        } elseif ($voted->state == $vote) {
            $voted->delete($voted->id);
            $voted = [
                'message' => 'cancelled vote'
            ];
        }
        $likeCount = Vote::where('post_id', $postId)
            ->where('state', 'like')
            ->count();
        $dislikeCount = Vote::where('post_id', $postId)
            ->where('state', 'dislike')
            ->count();
        $response = [
            'voted' => $voted,
            'like_count' => $likeCount,
            'dislike_count' => $dislikeCount
        ];
        return response($response, Response::HTTP_OK);
    }
}
