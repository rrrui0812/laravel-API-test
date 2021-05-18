<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PostsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['postMiddleware'])->except(['index', 'show', 'store', 'search']);
//        $this->middleware(['checkPostMiddleware'])->except(['store', 'update', 'destroy']);
    }

    public function index()
    {
        $commentCount = DB::table('comments')
            ->select('post_id', DB::raw('count(*) as comment_count'))
            ->groupBy('post_id');

        $likeCount = DB::table('votes')
            ->select('post_id', DB::raw('count(*) as like_count'))
            ->where('state', '=', 'like')
            ->groupBy('post_id');

        $dislikeCount = DB::table('votes')
            ->select('post_id', DB::raw('count(*) as dislike_count'))
            ->where('state', '=', 'dislike')
            ->groupBy('post_id');

        $posts = DB::table('posts')
            ->leftJoin('users', 'user_id', '=', 'users.id')
            ->leftjoinSub($commentCount, 'comment_count', function ($join) {
                $join->on('posts.id', '=', 'comment_count.post_id');
            })
            ->leftjoinSub($likeCount, 'like_count', function ($join) {
                $join->on('posts.id', '=', 'like_count.post_id');
            })
            ->leftjoinSub($dislikeCount, 'dislike_count', function ($join) {
                $join->on('posts.id', '=', 'dislike_count.post_id');
            })
            ->select(
                'posts.*',
                'users.name',
                'users.avatar',
                DB::Raw('IFNULL( `comment_count`.`comment_count` , 0 ) as comment_count'),
                DB::Raw('IFNULL( `like_count`.`like_count` , 0 ) as like_count'),
                DB::Raw('IFNULL( `dislike_count`.`dislike_count` , 0 ) as dislike_count'),
            )
            ->orderBy('posts.id')
            ->get();

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
        if (!$post) {
            return response('Not Found', Response::HTTP_NOT_FOUND);
        }
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

        $post->update($content);
        return response($post, Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $post = auth()->user()->posts()->find($id);

        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }
        $post->delete($id);
        return response($post, Response::HTTP_OK);
    }

    public function search($search)
    {
        $response = Post::where('title', 'like', '%' . $search . '%')
            ->orWhere('content', 'like', '%' . $search . '%')
            ->get();

        if ($response->isEmpty()) {
            return response('Not Found', Response::HTTP_NOT_FOUND);
        }
        return response($response, Response::HTTP_OK);
    }
}
