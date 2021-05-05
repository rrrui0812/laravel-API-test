<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentsController;

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

Route::get('/posts', [PostsController::class, 'index']);
Route::get('/posts/{post}', [PostsController::class, 'show']);

Route::get('/comments/{post_id}', [CommentsController::class, 'index']);
Route::get('/comments/{comment}', [CommentsController::class, 'show']);

//Route::group(['middleware' => ['auth:sanctum', 'postMiddleware']], function () {
Route::group(['middleware' => ['auth:sanctum']], function () {
    //這裡放需要驗證才能做的動作
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/posts', [PostsController::class, 'store']);
    Route::patch('/posts/{post}', [PostsController::class, 'update']);
    Route::delete('/posts/{post}', [PostsController::class, 'destroy']);

    Route::post('/comments/{post_id}', [CommentsController::class, 'store']);
    Route::patch('/comments/{comment}', [CommentsController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentsController::class, 'destroy']);

});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
