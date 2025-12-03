<?php

namespace Abdulbaset\Accessify\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        
        if (! $user) {
            abort(403, 'Unauthenticated.');
        }

        // Split the permissions by pipe
        $permissions = is_array($permission) 
            ? $permission 
            : explode('|', $permission);

        // Check if user has any of the permissions using hasAnyPermission
        if ($user->hasAnyPermission($permissions)) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
