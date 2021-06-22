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


        $token = $user->createToken('myapptoken')->plainTextToken;

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

//    public function showProfile()
//    {
//        $user = auth()->user();
//        return response($user, Response::HTTP_OK);
//    }

    public function updateProfile(Request $request)
    {

    }


    public function getProfile($userId)
    {

    }

}
