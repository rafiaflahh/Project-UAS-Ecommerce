<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->role === 'ADMIN') {
            return $next($request);
        }

        // If user is not admin, return 403 with a helpful message
        return response()->json([
            'error' => 'Access denied',
            'message' => 'Only administrators can access this resource.',
            'role' => Auth::check() ? Auth::user()->role : 'guest',
        ], 403);
    }
}