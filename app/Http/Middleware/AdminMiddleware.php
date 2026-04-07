<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // CENTRALIZED AUTH: Only allow users with the 'admin' role.
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request);
        }

        // REDIRECT FALLBACK: If not admin, throw 403.
        abort(403, 'Unauthorized. Admin Access Only.');
    }
}
