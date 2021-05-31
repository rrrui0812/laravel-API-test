<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $fields = $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'avatar' => 'nullable|mimes:jpg,jpeg,png|max:1024'
        ]);

        if ($request->has('avatar')) {
            $avatar = $request->file('avatar')->store('avatar');
        } else {
            $avatar = 'null';
        }

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'avatar' => $avatar
        ]);


        $token = $user->createToken('myapptken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, Response::HTTP_CREATED);

    }

    public function login(Request $request)
    {
        $fields = $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $fields['email'])->first();
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'massage' => 'The provided credentials are incorrect.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->delete();

        $token = $user->createToken('myapptken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, Response::HTTP_CREATED);

    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        $response = [
            'message' => 'Logged out'
        ];
        return response($response, Response::HTTP_OK);
    }

    public function showProfile()
    {
        $user = auth()->user();
        return response($user, Response::HTTP_OK);
    }

    public function updateProfile(Request $request)
    {
        $fields = $this->validate($request, [
            'name' => 'required|string',
            'avatar' => 'nullable|mimes:jpg,jpeg,png|max:1024'
        ]);

        if ($request->hasFile('avatar')) {
            if (auth()->user()->avatar != 'null') {
                Storage::disk('public')->delete(auth()->user()->avatar);
            }
            $avatar = $request->file('avatar')->store('avatar');
        } else {
            $avatar = auth()->user()->avatar;
        }

        auth()->user()->update([
            'name' => $fields['name'],
            'avatar' => $avatar
        ]);
        $user = auth()->user();
        return response($user, Response::HTTP_OK);
    }


    public function getProfile($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response('Not Found', Response::HTTP_NOT_FOUND);
        }

        $userPostCount = $user->posts()->count();
        $userCommentsCount = $user->comments()->count();

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
            ->where('user_id', $userId)
            ->select(
                'posts.*',
                'users.name',
                'users.avatar',
                DB::Raw('IFNULL( `comment_count`.`comment_count` , 0 ) as comment_count'),
                DB::Raw('IFNULL( `like_count`.`like_count` , 0 ) as like_count'),
                DB::Raw('IFNULL( `dislike_count`.`dislike_count` , 0 ) as dislike_count'),
            )
            ->orderBy('posts.id')
            ->paginate(10);

        $userTotalLikeCount = Vote::whereHas('votable', function ($voteModel) use ($user) {
            $voteModel->where('user_id', $user->getKey())
                ->where('state', 'like');
        })->count();

        $userTotalDislikeCount = Vote::whereHas('votable', function ($voteModel) use ($user) {
            $voteModel->where('user_id', $user->getKey())
                ->where('state', 'dislike');
        })->count();

        $userData = [
            'name' => $user->name,
            'avatar' => $user->avatar,
            'post_count' => $userPostCount,
            'comments_count' => $userCommentsCount,
            'like_count' => $userTotalLikeCount,
            'dislike_count' => $userTotalDislikeCount
        ];

        $response = [
            'user' => $userData,
            'posts' => $posts
        ];

        return response($response, Response::HTTP_OK);
    }

}
