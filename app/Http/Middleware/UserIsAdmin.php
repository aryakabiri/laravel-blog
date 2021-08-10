<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserIsAdmin
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
        if (auth()->user()->role !== 'admin') {
            $response = [
                'success' => false,
                'data' => [],
                'message' => 'you dont have access to this route.',
            ];
            return response()->json($response);
        }
        return $next($request);
    }
}
