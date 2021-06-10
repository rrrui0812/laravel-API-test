<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Image;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['postMiddleware'])->except(['index', 'show', 'store', 'search', 'test']);
    }

    public function index()
    {
        $commentCount = DB::table('comments')
            ->select('post_id', DB::raw('count(*) as comment_count'))
            ->groupBy('post_id');

        $likeCount = DB::table('votes')
            ->select('votable_id', DB::raw('count(*) as like_count'))
            ->where('votable_type', 'App\Models\Post')
            ->where('state', '=', 'like')
            ->groupBy('votable_id');

        $dislikeCount = DB::table('votes')
            ->select('votable_id', DB::raw('count(*) as dislike_count'))
            ->where('votable_type', 'App\Models\Post')
            ->where('state', '=', 'dislike')
            ->groupBy('votable_id');

        $posts = DB::table('posts')
            ->leftJoin('users', 'user_id', '=', 'users.id')
            ->leftjoinSub($commentCount, 'comment_count', function ($join) {
                $join->on('posts.id', '=', 'comment_count.post_id');
            })
            ->leftjoinSub($likeCount, 'like_count', function ($join) {
                $join->on('posts.id', '=', 'like_count.votable_id');
            })
            ->leftjoinSub($dislikeCount, 'dislike_count', function ($join) {
                $join->on('posts.id', '=', 'dislike_count.votable_id');
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
            'images.*' => 'nullable|mimes:jpg,jpeg,png|max:1024'
        ]);

        $content = [
            'title' => $request->input('title'),
            'content' => $request->input('content')
        ];
        $post = auth()->user()->posts()->create($content);

        $images = $request->file('images');
        if ($request->hasFile('images')) {
            foreach ($images as $key => $value) {
                $path = $images[$key]->store('images');
                $content = [
                    'user_id' => Auth::id(),
                    'title' => $key,
                    'path' => $path
                ];
                $post->images()->create($content);
            }
        }

        $getImages = $post->images()->select('id', 'title', 'path')->get();

        $response = [
            'post' => $post,
            'images' => $getImages
        ];

        return response($response, Response::HTTP_CREATED);
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
            ->select('votable_id', DB::raw('count(*) as like_count'))
            ->where('votable_type', 'App\Models\Post')
            ->where('state', '=', 'like')
            ->where('votable_id', '=', $id)
            ->groupBy('votable_id');

        $dislikeCount = DB::table('votes')
            ->select('votable_id', DB::raw('count(*) as dislike_count'))
            ->where('votable_type', 'App\Models\Post')
            ->where('state', '=', 'dislike')
            ->where('votable_id', '=', $id)
            ->groupBy('votable_id');

        $postData = DB::table('posts')
            ->where('posts.id', '=', $id)
            ->leftJoin('users', 'user_id', '=', 'users.id')
            ->leftjoinSub($commentCount, 'comment_count', function ($join) {
                $join->on('posts.id', '=', 'comment_count.post_id');
            })
            ->leftjoinSub($likeCount, 'like_count', function ($join) {
                $join->on('posts.id', '=', 'like_count.votable_id');
            })
            ->leftjoinSub($dislikeCount, 'dislike_count', function ($join) {
                $join->on('posts.id', '=', 'dislike_count.votable_id');
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
            ->select('votable_id', DB::raw('count(*) as like_count'))
            ->where('votable_type', 'App\Models\Comment')
            ->where('state', '=', 'like')
            ->groupBy('votable_id');

        $commentDislikeCount = DB::table('votes')
            ->select('votable_id', DB::raw('count(*) as dislike_count'))
            ->where('votable_type', 'App\Models\Comment')
            ->where('state', '=', 'dislike')
            ->groupBy('votable_id');

        $commentsData = DB::table('comments')
            ->where('comments.post_id', '=', $id)
            ->leftJoin('users', 'user_id', '=', 'users.id')
            ->leftjoinSub($commentLikeCount, 'like_count', function ($join) {
                $join->on('comments.id', '=', 'like_count.votable_id');
            })
            ->leftjoinSub($commentDislikeCount, 'dislike_count', function ($join) {
                $join->on('comments.id', '=', 'dislike_count.votable_id');
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

        $images = $post->images()->select('id', 'title', 'path')->get();

        $response = [
            'post' => $postData,
            'images' => $images,
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
            ->select('votable_id', DB::raw('count(*) as like_count'))
            ->where('votable_type', 'App\Models\Post')
            ->where('state', '=', 'like')
            ->where('votable_id', '=', $id)
            ->groupBy('votable_id');

        $dislikeCount = DB::table('votes')
            ->select('votable_id', DB::raw('count(*) as dislike_count'))
            ->where('votable_type', 'App\Models\Post')
            ->where('state', '=', 'dislike')
            ->where('votable_id', '=', $id)
            ->groupBy('votable_id');

        $postData = DB::table('posts')
            ->where('posts.id', '=', $id)
            ->leftJoin('users', 'user_id', '=', 'users.id')
            ->leftjoinSub($commentCount, 'comment_count', function ($join) {
                $join->on('posts.id', '=', 'comment_count.post_id');
            })
            ->leftjoinSub($likeCount, 'like_count', function ($join) {
                $join->on('posts.id', '=', 'like_count.votable_id');
            })
            ->leftjoinSub($dislikeCount, 'dislike_count', function ($join) {
                $join->on('posts.id', '=', 'dislike_count.votable_id');
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
            ->select('votable_id', DB::raw('count(*) as like_count'))
            ->where('votable_type', 'App\Models\Comment')
            ->where('state', '=', 'like')
            ->groupBy('votable_id');

        $commentDislikeCount = DB::table('votes')
            ->select('votable_id', DB::raw('count(*) as dislike_count'))
            ->where('votable_type', 'App\Models\Comment')
            ->where('state', '=', 'dislike')
            ->groupBy('votable_id');

        $commentsData = DB::table('comments')
            ->where('comments.post_id', '=', $id)
            ->leftJoin('users', 'user_id', '=', 'users.id')
            ->leftjoinSub($commentLikeCount, 'like_count', function ($join) {
                $join->on('comments.id', '=', 'like_count.votable_id');
            })
            ->leftjoinSub($commentDislikeCount, 'dislike_count', function ($join) {
                $join->on('comments.id', '=', 'dislike_count.votable_id');
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
        $post = auth()->user()->posts->find($id);

        $post->votes->each->delete();
        $post->commentsVotes->each->delete();
        $post->comments->each->delete();

        $images = $post->images;
        if (!$images->isEmpty()) {
            foreach ($images as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }
        }

        $post->delete($id);

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
            ->select('votable_id', DB::raw('count(*) as like_count'))
            ->where('votable_type', 'App\Models\Post')
            ->where('state', '=', 'like')
            ->groupBy('votable_id');

        $dislikeCount = DB::table('votes')
            ->select('votable_id', DB::raw('count(*) as dislike_count'))
            ->where('votable_type', 'App\Models\Post')
            ->where('state', '=', 'dislike')
            ->groupBy('votable_id');

        $postData = DB::table('posts')
            ->where('posts.title', 'like', '%' . $search . '%')
            ->orwhere('posts.content', 'like', '%' . $search . '%')
            ->leftJoin('users', 'user_id', '=', 'users.id')
            ->leftjoinSub($commentCount, 'comment_count', function ($join) {
                $join->on('posts.id', '=', 'comment_count.post_id');
            })
            ->leftjoinSub($likeCount, 'like_count', function ($join) {
                $join->on('posts.id', '=', 'like_count.votable_id');
            })
            ->leftjoinSub($dislikeCount, 'dislike_count', function ($join) {
                $join->on('posts.id', '=', 'dislike_count.votable_id');
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

    public function test($id)
    {

    }
}
