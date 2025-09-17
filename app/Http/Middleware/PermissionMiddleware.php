<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'Authentication required'
            ], 401);
        }

        $user = auth()->user();
        
        // Check if user has any of the required permissions
        if (!$user->hasAnyPermission($permissions)) {
            return response()->json([
                'message' => 'Access denied.',
                'error' => 'Insufficient permissions',
                'required_permissions' => $permissions,
                'user_permissions' => $user->getAllPermissions()->pluck('name')
            ], 403);
        }

        return $next($request);
    }
}
