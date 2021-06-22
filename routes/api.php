<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\VotesController;
use App\Http\Controllers\ProfileController;

use App\Http\Resources\PostResource;

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

//AuthController
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//ProfileController
Route::get('/profile/{user_id}', [ProfileController::class, 'show']);

//PostsController
Route::get('/posts', [PostsController::class, 'index']);
Route::get('/posts/{post}', [PostsController::class, 'show']);
//Route::get('/posts/{post}', function (\App\Models\Post $post) {
//    return $post;
//});
Route::get('/posts/search/{search}', [PostsController::class, 'search']);
//測試用route
Route::get('/posts/test/{post_id}', [PostsController::class, 'test']);

//Route::get('/posts/test/{post_id}', function () {
//    return PostResource::collection(\App\Models\Post::paginate(5));
//});
//Route::get('/posts/test/{post_id}', function () {
//    return new PostResource(\App\Models\Post::paginate(5));
//});

Route::group(['middleware' => ['auth:sanctum']], function () {
//    Route::get('/posts/test/{post_id}', [PostsController::class, 'test']);

    //這裡放需要Token才能做的動作
    //AuthController
    Route::post('/logout', [AuthController::class, 'logout']);

    //ProfileController
    Route::patch('/profile', [ProfileController::class, 'update']);

    //PostsController
    Route::post('/posts', [PostsController::class, 'store']);
    Route::patch('/posts/{post}', [PostsController::class, 'update']);
    Route::delete('/posts/{post}', [PostsController::class, 'destroy']);

    //CommentsController
    Route::post('/comments/{post_id}', [CommentsController::class, 'store']);
    Route::patch('/comments/comment/{comment}', [CommentsController::class, 'update']);
    Route::delete('/comments/comment/{comment}', [CommentsController::class, 'destroy']);

    //VotesController
//    Route::post('/votes/{post_id}/{vote}', [VotesController::class, 'vote']);
    Route::post('/votes/{votable_type}/{votable_id}/{state}', [VotesController::class, 'vote']);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
