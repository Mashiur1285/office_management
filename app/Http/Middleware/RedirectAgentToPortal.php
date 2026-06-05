<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectAgentToPortal
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->hasRole('agent')) {
            // Allow agent-portal routes and auth routes (logout etc.)
            $allowed = $request->routeIs('agent-portal.*')
                || $request->routeIs('logout')
                || $request->routeIs('password.*')
                || $request->routeIs('verification.*');

            if (! $allowed) {
                return redirect()->route('agent-portal.dashboard');
            }
        }

        return $next($request);
    }
}
