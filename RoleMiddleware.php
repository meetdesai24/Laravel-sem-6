<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: ->middleware('role:admin') or 'role:admin,security'.
 * Also signs out accounts the Admin has deactivated.
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->status) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account is deactivated. Contact the administrator.']);
        }

        if (! $user->hasRole(...$roles)) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not allowed to open that page.');
        }

        return $next($request);
    }
}
