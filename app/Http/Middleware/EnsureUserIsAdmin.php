<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\User;
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
        /** @var User|null $user */
        $user = $request->user();

        if ($user && $user->role === UserRole::ADMIN) {
            return $next($request);
        }

        // We return a 403 (Forbidden) to indicate the user is
        // authenticated but lacks the necessary permissions.
        abort(403);
    }
}
