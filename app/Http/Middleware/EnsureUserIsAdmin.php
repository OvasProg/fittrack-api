<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A security gate that only lets admins pass through.
 *
 * This middleware checks the user's role before allowing
 * them to access sensitive routes like the audit logs
 * or global announcement settings.
 */
class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->role === 'admin') {
            return $next($request);
        }

        // We return a 404 (Not Found) instead of a 403 (Forbidden).
        // This is a "stealth" security move so that unauthorized
        // people don't even know the admin URL exists.
        abort(404);
    }
}
