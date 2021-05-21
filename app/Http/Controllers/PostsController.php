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
        $commentCount = DB::table('comments')
            ->select('post_id', DB::raw('count(*) as comment_count'))
            ->groupBy('post_id');

        $likeCount = DB::table('votes')
            ->select('voteable_id', DB::raw('count(*) as like_count'))
            ->where('voteable_type','App\Models\Post')
            ->where('state', '=', 'like')
            ->groupBy('voteable_id');

        $dislikeCount = DB::table('votes')
            ->select('voteable_id', DB::raw('count(*) as dislike_count'))
            ->where('voteable_type','App\Models\Post')
            ->where('state', '=', 'dislike')
            ->groupBy('voteable_id');

        $posts = DB::table('posts')
            ->leftJoin('users', 'user_id', '=', 'users.id')
            ->leftjoinSub($commentCount, 'comment_count', function ($join) {
                $join->on('posts.id', '=', 'comment_count.post_id');
            })
            ->leftjoinSub($likeCount, 'like_count', function ($join) {
                $join->on('posts.id', '=', 'like_count.voteable_id');
            })
            ->leftjoinSub($dislikeCount, 'dislike_count', function ($join) {
                $join->on('posts.id', '=', 'dislike_count.voteable_id');
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
            'image' => 'nullable|mimes:jpg,jpeg,png|max:1024'
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

        $commentCount = DB::table('comments')
            ->select('post_id', DB::raw('count(*) as comment_count'))
            ->where('post_id', '=', $id)
            ->groupBy('post_id');

        $likeCount = DB::table('votes')
            ->select('voteable_id', DB::raw('count(*) as like_count'))
            ->where('voteable_type','App\Models\Post')
            ->where('state', '=', 'like')
            ->where('voteable_id', '=', $id)
            ->groupBy('voteable_id');

        $dislikeCount = DB::table('votes')
            ->select('voteable_id', DB::raw('count(*) as dislike_count'))
            ->where('voteable_type','App\Models\Post')
            ->where('state', '=', 'dislike')
            ->where('voteable_id', '=', $id)
            ->groupBy('voteable_id');

        $postData = DB::table('posts')
            ->where('posts.id', '=', $id)
            ->leftJoin('users', 'user_id', '=', 'users.id')
            ->leftjoinSub($commentCount, 'comment_count', function ($join) {
                $join->on('posts.id', '=', 'comment_count.post_id');
            })
            ->leftjoinSub($likeCount, 'like_count', function ($join) {
                $join->on('posts.id', '=', 'like_count.voteable_id');
            })
            ->leftjoinSub($dislikeCount, 'dislike_count', function ($join) {
                $join->on('posts.id', '=', 'dislike_count.voteable_id');
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
            ->first();

        $commentLikeCount = DB::table('votes')
            ->select('voteable_id', DB::raw('count(*) as like_count'))
            ->where('voteable_type','App\Models\Comment')
            ->where('state', '=', 'like')
            ->groupBy('voteable_id');

        $commentDislikeCount = DB::table('votes')
            ->select('voteable_id', DB::raw('count(*) as dislike_count'))
            ->where('voteable_type','App\Models\Comment')
            ->where('state', '=', 'dislike')
            ->groupBy('voteable_id');

        $commentsData = DB::table('comments')
            ->where('comments.post_id', '=', $id)
            ->leftJoin('users', 'user_id', '=', 'users.id')
            ->leftjoinSub($commentLikeCount, 'like_count', function ($join) {
                $join->on('comments.id', '=', 'like_count.voteable_id');
            })
            ->leftjoinSub($commentDislikeCount, 'dislike_count', function ($join) {
                $join->on('comments.id', '=', 'dislike_count.voteable_id');
            })
            ->select(
                'comments.*',
                'users.name',
                'users.avatar',
                DB::Raw('IFNULL( `like_count`.`like_count` , 0 ) as like_count'),
                DB::Raw('IFNULL( `dislike_count`.`dislike_count` , 0 ) as dislike_count'),
            )
            ->orderBy('comments.id')
            ->get();

        $response = [
            'post' => $postData,
            'comments' => $commentsData
        ];
        return response($response, Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        $post = auth()->user()->posts()->find($id);

        $this->validate($request, [
            'title' => 'required',
            'content' => 'required',
            'image' => 'nullable|mimes:jpg,jpeg,png|max:1024'
        ]);

        if ($request->hasFile('image')) {
            if ($post->image != 'null') {
                Storage::disk('public')->delete($post->image);
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

        $commentCount = DB::table('comments')
            ->select('post_id', DB::raw('count(*) as comment_count'))
            ->where('post_id', '=', $id)
            ->groupBy('post_id');

        $likeCount = DB::table('votes')
            ->select('voteable_id', DB::raw('count(*) as like_count'))
            ->where('voteable_type','App\Models\Post')
            ->where('state', '=', 'like')
            ->where('voteable_id', '=', $id)
            ->groupBy('voteable_id');

        $dislikeCount = DB::table('votes')
            ->select('voteable_id', DB::raw('count(*) as dislike_count'))
            ->where('voteable_type','App\Models\Post')
            ->where('state', '=', 'dislike')
            ->where('voteable_id', '=', $id)
            ->groupBy('voteable_id');

        $postData = DB::table('posts')
            ->where('posts.id', '=', $id)
            ->leftJoin('users', 'user_id', '=', 'users.id')
            ->leftjoinSub($commentCount, 'comment_count', function ($join) {
                $join->on('posts.id', '=', 'comment_count.post_id');
            })
            ->leftjoinSub($likeCount, 'like_count', function ($join) {
                $join->on('posts.id', '=', 'like_count.voteable_id');
            })
            ->leftjoinSub($dislikeCount, 'dislike_count', function ($join) {
                $join->on('posts.id', '=', 'dislike_count.voteable_id');
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
            ->first();

        $commentLikeCount = DB::table('votes')
            ->select('voteable_id', DB::raw('count(*) as like_count'))
            ->where('voteable_type','App\Models\Comment')
            ->where('state', '=', 'like')
            ->groupBy('voteable_id');

        $commentDislikeCount = DB::table('votes')
            ->select('voteable_id', DB::raw('count(*) as dislike_count'))
            ->where('voteable_type','App\Models\Comment')
            ->where('state', '=', 'dislike')
            ->groupBy('voteable_id');

        $commentsData = DB::table('comments')
            ->where('comments.post_id', '=', $id)
            ->leftJoin('users', 'user_id', '=', 'users.id')
            ->leftjoinSub($commentLikeCount, 'like_count', function ($join) {
                $join->on('comments.id', '=', 'like_count.voteable_id');
            })
            ->leftjoinSub($commentDislikeCount, 'dislike_count', function ($join) {
                $join->on('comments.id', '=', 'dislike_count.voteable_id');
            })
            ->select(
                'comments.*',
                'users.name',
                'users.avatar',
                DB::Raw('IFNULL( `like_count`.`like_count` , 0 ) as like_count'),
                DB::Raw('IFNULL( `dislike_count`.`dislike_count` , 0 ) as dislike_count'),
            )
            ->orderBy('comments.id')
            ->get();


        $response = [
            'post' => $postData,
            'comments' => $commentsData
        ];

        return response($response, Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $post = auth()->user()->posts()->find($id);

        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }
        $post->delete($id);
        $post->comments()->where('post_id', $id)->delete();
        $post->vote()->where('post_id', $id)->delete();
        $response = [
            'message' => 'Post Has Deleted.'
        ];

        return response($response, Response::HTTP_OK);
    }

    public function search($search)
    {
        $commentCount = DB::table('comments')
            ->select('post_id', DB::raw('count(*) as comment_count'))
            ->groupBy('post_id');

        $likeCount = DB::table('votes')
            ->select('voteable_id', DB::raw('count(*) as like_count'))
            ->where('voteable_type','App\Models\Post')
            ->where('state', '=', 'like')
            ->groupBy('voteable_id');

        $dislikeCount = DB::table('votes')
            ->select('voteable_id', DB::raw('count(*) as dislike_count'))
            ->where('voteable_type','App\Models\Post')
            ->where('state', '=', 'dislike')
            ->groupBy('voteable_id');

        $postData = DB::table('posts')
            ->where('posts.title', 'like', '%' . $search . '%')
            ->orwhere('posts.content', 'like', '%' . $search . '%')
            ->leftJoin('users', 'user_id', '=', 'users.id')
            ->leftjoinSub($commentCount, 'comment_count', function ($join) {
                $join->on('posts.id', '=', 'comment_count.post_id');
            })
            ->leftjoinSub($likeCount, 'like_count', function ($join) {
                $join->on('posts.id', '=', 'like_count.voteable_id');
            })
            ->leftjoinSub($dislikeCount, 'dislike_count', function ($join) {
                $join->on('posts.id', '=', 'dislike_count.voteable_id');
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

        if ($postData->isEmpty()) {
            $response = [
                'message' => 'Not Found.'
            ];
            return response($response, Response::HTTP_NOT_FOUND);
        }
        return response($postData, Response::HTTP_OK);
    }
}
