<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use App\Policies\AdminDashboardPolicy;
use App\Policies\AnalyticsPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

/**
 * The central hub for configuring how our application behaves.
 *
 * This provider is where we set up high-level rules, like how
 * password reset links are built and how fast users are allowed
 * to send requests to our API.
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Implicitly grant "Super Admin" role all permissions globally
        Gate::before(function (User $user, string $ability) {
            if ($user->role === UserRole::ADMIN) {
                // Admin shouldn't be able to change their own role.
                if ($ability === 'updateRole') {
                    return null;
                }

                return true;
            }
        });
        Gate::define('viewProStats', [AnalyticsPolicy::class, 'viewProStats']);
        Gate::define('viewAdminDashboard', [AdminDashboardPolicy::class, 'view']);

        // We override the default password reset URL so that users are
        // sent to our custom frontend instead of a default
        // Laravel blade view.
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url') .
                "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        // This is our standard "speed limit." It prevents a single user
        // from flooding the server with more than 60 requests per minute.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Creating workouts and generating adaptive plans is "heavy" work
        // for our database. We limit this to 10 per minute to ensure
        // the server stays fast for everyone else.
        RateLimiter::for('workouts', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}
