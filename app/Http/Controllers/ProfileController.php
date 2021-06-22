<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\UserCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show($userId)
    {
        User::findOrFail($userId);

        $query = User::with('posts', 'posts.images')
            ->withCount(
                'posts',
                'comments',
                'totalLikePostsVotes',
                'totalDislikePostsVotes',
                'totalLikeCommentsVotes',
                'totalDislikeCommentsVotes',
            )->find($userId);

        return UserResource::make($query)->response()->setStatusCode(Response::HTTP_OK);
//        $response = [
//            'user' => $userData,
//            'posts' => $posts
//        ];
//        return response($response, Response::HTTP_OK);
    }

    public function update(Request $request)
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

}
