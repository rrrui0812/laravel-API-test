<?php

namespace App\Http\Middleware;

use App\Models\Post;
use Closure;
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
//        $user = auth()->user()->getKey();
//        $user_id = $user->id;
        if (auth()->user()->getKey() === Post::find($postId)->user_id) {
            return $next($request);
//            return response('TEST', 200);
        }
//        return response('ERROR', 404);
        return response('Not Found.', Response::HTTP_NOT_FOUND);
    }
}
