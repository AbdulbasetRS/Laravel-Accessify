<?php

namespace Abdulbaset\Accessify\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleOrPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $roleOrPermission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $roleOrPermission): Response
    {
        $user = $request->user();
        
        if (! $user) {
            abort(403, 'Unauthenticated.');
        }

        // Split the roles and permissions by |
        $rolesOrPermissions = is_array($roleOrPermission) 
            ? $roleOrPermission 
            : explode('|', $roleOrPermission);

        // Check if user has any of the roles or permissions
        if ($user->hasAnyRole($rolesOrPermissions) || $user->hasAnyPermission($rolesOrPermissions)) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
