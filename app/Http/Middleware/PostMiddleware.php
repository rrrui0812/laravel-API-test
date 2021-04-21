<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PostMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
//        $request->user(); //login user == auth()->user() === Auth::user()
//        $request->??('post'); //post ids
        return $next($request);
    }
}
