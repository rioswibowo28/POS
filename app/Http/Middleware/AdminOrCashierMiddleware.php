<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrCashierMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $user = auth()->user();

        if (!$user->isAdmin()) {
            if (!$user->isCashier()) {
                abort(403, 'Unauthorized. Admin or Cashier access only.');
            }
            
            // Check if route is related to reports
            if ($request->is('reports') || $request->is('reports/*') || $request->is('dynamic-reports') || $request->is('dynamic-reports/*')) {
                // If user is cashier but setting is disabled, abort
                if (!$user->canAccessReports()) {
                    abort(403, 'Unauthorized. Report access is disabled for cashiers. Please contact administrator.');
                }
            }
        }

        return $next($request);
    }
}