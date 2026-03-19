<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If the user is authenticated and is an admin, let them pass
        if ($request->user() && $request->user()->role === 'admin') {
            return $next($request);
        }

        // Otherwise, throw a 404 Not Found to completely hide the route
        abort(404);
    }
}
