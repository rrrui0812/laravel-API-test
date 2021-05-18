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
        $postId = $request->route('post');

        if (!Post::find($postId)) {
            $response = [
                'message' => 'Not Found.'
            ];
            return response($response, Response::HTTP_NOT_FOUND);
        }

        if (Auth::id() === Post::find($postId)->user_id) {
            return $next($request);
        }

        $response = [
            'message' => 'Not Found.'
        ];
        return response($response, Response::HTTP_NOT_FOUND);
    }

}
