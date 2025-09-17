<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'Authentication required'
            ], 401);
        }

        $user = auth()->user();
        
        // Check if user has any of the required roles
        if (!$user->hasAnyRole($roles)) {
            return response()->json([
                'message' => 'Access denied.',
                'error' => 'Insufficient permissions',
                'required_roles' => $roles,
                'user_roles' => $user->getRoleNames()
            ], 403);
        }

        return $next($request);
    }
}
