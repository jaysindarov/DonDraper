<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs out authenticated users who have been inactive for more than 10 minutes.
 *
 * Stores a Unix timestamp in the session on every request and compares it on
 * the next request. If the gap exceeds the timeout, the session is invalidated
 * and the user is redirected to the login page with an explanatory message.
 */
class CheckSessionActivity
{
    private const TIMEOUT_SECONDS = 600; // 10 minutes

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = $request->session()->get('last_activity_at');

            if ($lastActivity && (time() - $lastActivity) > self::TIMEOUT_SECONDS) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('status', 'Your session expired due to inactivity. Please log in again.');
            }

            $request->session()->put('last_activity_at', time());
        }

        return $next($request);
    }
}
