<?php

namespace App\Http\Middleware;

use Closure;
//use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CheckPostMiddleware
{
    /**
     * Handle an incoming request.
     *s
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
//        if (isset($response)){
//            return response('404 CCCC',Response::HTTP_NOT_FOUND);
//        }
        echo $response;
        var_dump($response);
        return $response;
    }
}
