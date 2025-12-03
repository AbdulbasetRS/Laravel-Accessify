<?php

namespace Abdulbaset\Accessify\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();
        
        if (! $user) {
            abort(403, 'Unauthenticated.');
        }

        // Split the roles by pipe
        $roles = is_array($role) 
            ? $role 
            : explode('|', $role);

        // Check if user has any of the roles using hasAnyRole
        if ($user->hasAnyRole($roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
