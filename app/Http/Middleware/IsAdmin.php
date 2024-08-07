<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (auth()->user()) {

            if (auth()->user()->user_role == 'admin') {
                return $next($request);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'You are not an admin!'
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Log In Required!'
            ]);
        }
    }
}