<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ends the session of an account that has been deactivated.
 *
 * AuthController already refuses a deactivated account at sign-in, and every
 * ability denies for one. This closes the remaining gap: a user who was
 * already signed in when they were deactivated would otherwise keep the
 * dashboard and their own change-password form until they logged out.
 *
 * Registered on the web group, so it runs before any route's can: gate.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Guests pass straight through. Before the is_active column exists the
        // accessor returns true, so this never fires — deploy-order safe.
        if ($user && !$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // No redirect loop: the next request to /login has no user.
            return redirect()->route('login')
                ->withErrors(['email' => 'This account has been deactivated.']);
        }

        return $next($request);
    }
}
