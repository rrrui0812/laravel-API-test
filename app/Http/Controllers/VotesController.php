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
        // 判斷 User 是對 Post 還是 Comment 做 Vote
        // $votableType : [Post,Comment]
        // $votableId : 欲 Vote 的 Post/Comment 之 ID
        // $state : [like,dislike]
        switch ($votableType) {
            case 'Post':
                $votable = Post::with('votes')->find($votableId);
                break;
            case 'Comment':
                $votable = Comment::with('votes')->find($votableId);
                break;
        }
        // 例外處理
        if (!$votable) {
            $response = [
                'message' => $votableType . ' Not Found.'
            ];
            return response($response, Response::HTTP_NOT_FOUND);
        }
        // 多態關聯，找出這 User 是否 Vote 過
        $voted = $votable->votes()->where('user_id', Auth::id())->first();

        // if : 如果沒 Vote 過，就建立一筆 Vote
        // elseif : 如果 $state 與之前紀錄 不符 ，則 更新 Vote
        // elseif : 如果 $state 與之前紀錄 相符 ，則 刪除 Vote
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
        $votable->loadCount([
            'votes as like_count' => function ($query) {
                $query->where('state', 'like');
            },
            'votes as dislike_count' => function ($query) {
                $query->where('state', 'dislike');
            }]);
        $response = [
            'voted' => $voted,
            'like_count' => $votable->like_count,
            'dislike_count' => $votable->dislike_count
        ];

        return response($response, Response::HTTP_OK);
    }
}
