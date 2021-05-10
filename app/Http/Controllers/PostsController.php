<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['postMiddleware'])->except(['index', 'show', 'store', 'search']);
    }

    public function index()
    {
//        $posts = Post::all();
        $names = DB::table('users')->select('name');
        $posts = DB::table('users')->select('email')->union($names)->get();
        return response($posts, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'content' => 'required',
            'image' => 'nullable|mimes:jpg,jpeg,png'
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('images');
        } else {
            $image = 'null';
        }

        $content = [
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'image' => $image
        ];
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
        $post = auth()->user()->posts()->find($id);

        $this->validate($request, [
            'title' => 'required',
            'content' => 'required',
            'image' => 'nullable|mimes:jpg,jpeg,png'
        ]);

        if ($request->hasFile('image')) {
            if ($post->image) {
                $image = Storage::disk('public')->delete($post->image);
            }
            $image = $request->file('image')->store('images');
        } else {
            $image = $post->image;
        }

        $content = [
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'image' => $image
        ];
//        $post = Post::find($id);
        $post->update($content);
        return response($post, Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $post = auth()->user()->posts()->find($id);
//        Post::find($id)->delete($id);
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }
        $post->delete($id);
        return response($post, Response::HTTP_OK);
    }

    public function search(Request $request)
    {
        $this->validate($request, [
            'content' => 'required',
        ]);
        $response = Post::where('title', 'like', '%' . $request->content . '%')
            ->orWhere('content', 'like', '%' . $request->content . '%')
            ->get();
        return response($response,Response::HTTP_OK);
    }
}
