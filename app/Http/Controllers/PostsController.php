<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Post;

class PostsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['postMiddleware'])->except(['show', 'store', 'index']);
    }

    public function index()
    {
        $posts = Post::all();
        return response($posts, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $content = $request->validate([
            'title' => 'required',
            'content' => 'required'
        ]);
        $post = auth()->user()->posts()->create($content);
        return response($post, Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $post = Post::find($id);
        return response($post, Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        $post = auth()->user()->posts->find($id);
        $content = $request->validate([
            'title' => 'required',
            'content' => 'required'
        ]);
//        $post = Post::find($id);
        $post->update($content);
        return response($post, Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $post = auth()->user()->posts->find($id);
//        Post::find($id)->delete($id);
        $post->delete($id);
        return response($post, Response::HTTP_OK);
    }
}
