<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Comment;
use App\Http\Resources\Comment as CommentResource;

class CommentsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['commentMiddleware'])->except(['store']);
    }

    public function store(Request $request, $postId)
    {
        $this->validate($request, [
            'content' => 'required'
        ]);
        $content = [
            'post_id' => $postId,
            'content' => $request->input('content')
        ];

        $comment = auth()->user()->comments()->create($content);

        return CommentResource::make($comment)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $comment = auth()->user()->comments()->find($id);
        $this->validate($request, [
            'content' => 'required'
        ]);
        $content = [
            'content' => $request->input('content')
        ];
        $comment->update($content);
        return CommentResource::make($comment)->response()->setStatusCode(Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $comment = auth()->user()->comments()->find($id);
        $comment->delete($id);
        return response('Comment has deleted', Response::HTTP_OK);
    }
}
