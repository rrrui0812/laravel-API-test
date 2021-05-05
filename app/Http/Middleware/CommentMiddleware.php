<?php

namespace App\Http\Middleware;

use App\Models\Comment;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CommentMiddleware
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
        $commentId=$request->route('comment');
        if (Auth::id() === Comment::find($commentId)->user_id) {
            return $next($request);
        }
        return response('Not Found.',Response::HTTP_NOT_FOUND);
    }
}
