<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostsController extends Controller
{
//    public function __construct()
//    {
//        $this->middleware(['postMiddleware'])->except(['show', 'index']);
//
//    }

    public function index()
    {
        return Post::all();
    }

    public function store(Request $request)
    {
        $content = $request->validate([
            'title' => 'required',
            'content' => 'required'
        ]);
        return auth()->user()->posts()->create($content);
    }

    public function show($id)
    {
        return Post::find($id);
    }

    public function update(Request $request, $id)
    {
        $post = auth()->user()->posts->find($id);
        $content = $request->validate([
            'title' => 'required',
            'content' => 'required'
        ]);
//        Post::find($id)->update($content);
        $post->update($content);
        return $post;
    }

    public function destroy($id)
    {
        $post = auth()->user()->posts->find($id);
//        abort_if(is_null($post), \Illuminate\Http\Response::HTTP_NOT_FOUND);
//        Post::find($id)->delete($id);
//        $post->delete($id);
        return response(['data' => $post]);
    }
}
