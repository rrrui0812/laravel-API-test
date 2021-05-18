<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\VotesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::apiResource('posts', PostsController::class);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/profile/{user_id}', [AuthController::class, 'profile']);

Route::get('/posts', [PostsController::class, 'index']);
Route::get('/posts/{post}', [PostsController::class, 'show']);
//Route::get('/posts/{post}', function (\App\Models\Post $post) {
//    return $post;
//});
Route::get('/posts/search/{search}', [PostsController::class, 'search']);

Route::get('/comments/{post_id}', [CommentsController::class, 'index']);
Route::get('/comments/comment/{comment}', [CommentsController::class, 'show']);

//Route::get();

//Route::group(['middleware' => ['auth:sanctum', 'postMiddleware']], function () {
Route::group(['middleware' => ['auth:sanctum']], function () {
    //這裡放需要驗證才能做的動作
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/posts', [PostsController::class, 'store']);
    Route::patch('/posts/{post}', [PostsController::class, 'update']);
    Route::delete('/posts/{post}', [PostsController::class, 'destroy']);

    Route::post('/comments/{post_id}', [CommentsController::class, 'store']);
    Route::patch('/comments/comment/{comment}', [CommentsController::class, 'update']);
    Route::delete('/comments/comment/{comment}', [CommentsController::class, 'destroy']);

    Route::post('/votes/{post_id}/{vote}', [VotesController::class, 'vote']);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
