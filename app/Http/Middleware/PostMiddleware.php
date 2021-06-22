<?php

namespace App\Http\Middleware;

use App\Models\Post;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

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
        $post = Post::find($request->route('post'));

        if (!$post || Auth::id() != $post->user_id) {
            $response = [
                'message' => 'Not Found.'
            ];
            return response($response, Response::HTTP_NOT_FOUND);
        }

        if (Auth::id() === $post->user_id) {
            return $next($request);
        }

        $response = [
            'message' => 'Not Found.'
        ];
        return response($response, Response::HTTP_NOT_FOUND);
    }

}
