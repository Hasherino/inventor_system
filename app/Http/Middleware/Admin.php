<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Admin
{
    public function handle(Request $request, Closure $next) {
        if(auth::User()->role != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Not authorized'
            ], 401);
        } else {
            return $next($request);
        }
    }
}
