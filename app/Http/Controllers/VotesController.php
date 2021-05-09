<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Vote;
use Illuminate\Support\Facades\Auth;

class VotesController extends Controller
{
    public function like($postId, $vote)
    {
        $like = Vote::where('user_id', Auth::id())->where('post_id', $postId)->first();
        if (is_null($like)) {
            $content = [
                'post_id' => $postId,
                'state' => $vote
            ];
            $like = auth()->user()->votes()->create($content);
        } else {
            $like->delete($like->id);
        }
        return response($like, Response::HTTP_OK);
    }
}
