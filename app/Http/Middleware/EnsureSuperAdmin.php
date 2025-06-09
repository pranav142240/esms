<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. Superadmin privileges required.'], 403);
            }
            return redirect()->route('login')->with('error', 'You must be a superadmin to access this area.');
        }
        
        return $next($request);
    }
}
