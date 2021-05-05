<?php

namespace App\Http\Middleware;

use App\Models\Post;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PostMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $postId = $request->route('post');
//        $post = Post::find($postId)->user_id;
//        $post_id = $post->user_id;
//        $user = auth()->user();
//        $user_id = $user->id;
//        if (auth()->user()->getKey() === Post::find($postId)->user_id) {
        if (Auth::id() === Post::find($postId)->user_id) {
            return $next($request);
        }
        return response('Not Found.', Response::HTTP_NOT_FOUND);
    }
}
