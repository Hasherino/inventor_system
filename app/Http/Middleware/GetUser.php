<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class GetUser
{
    public function handle(Request $request, Closure $next)
    {
        $request->user = JWTAuth::parseToken()->authenticate();

        return $next($request);
    }
}
