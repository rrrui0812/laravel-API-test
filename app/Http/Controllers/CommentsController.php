<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Comment;

class CommentsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['commentMiddleware'])->except(['index', 'store', 'show']);
    }

    public function index($postId)
    {
        $comments = Comment::where('post_id', $postId)->get();
        return response($comments, Response::HTTP_OK);
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

        return response($comment, Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $comment = Comment::where('id', $id)->get();
        return response($comment, Response::HTTP_OK);
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
        return response($comment, Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $comment = auth()->user()->comments()->find($id);
        $comment->delete($id);
        return response('Data deleted', Response::HTTP_OK);
    }
}
