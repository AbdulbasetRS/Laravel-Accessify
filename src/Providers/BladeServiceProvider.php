<?php

namespace Abdulbaset\Accessify\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;

/**
 * BladeServiceProvider
 *
 * This service provider registers Blade directives for role and permission checks.
 * It provides convenient @directives that can be used in Blade templates
 * to conditionally display content based on user roles and permissions.
 *
 * @package Abdulbaset\Accessify\Providers
 * @author Abdulbaset R. Sayed
 * @link https://github.com/AbdulbasetRS/laravel-accessify
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed
 * @version 1.0.0
 * @license MIT
 */
class BladeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     * Registers Blade directives for role and permission checks.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * @role('admin')
         * 
         * Checks if the authenticated user has the specified role.
         * Example: @role('admin') ... @endrole
         */
        Blade::if('role', function ($role) {
            $user = Auth::user();
            return $user && method_exists($user, 'hasRole') && $user->hasRole($role);
        });

        /**
         * @hasanyrole(['admin', 'editor'])
         * 
         * Checks if the authenticated user has any of the specified roles.
         * Example: @hasanyrole(['admin', 'editor']) ... @endhasanyrole
         */
        Blade::if('hasanyrole', function ($roles) {
            $user = Auth::user();
            return $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(is_array($roles) ? $roles : func_get_args());
        });

        /**
         * @haspermission('create-posts')
         * 
         * Checks if the authenticated user has the specified permission.
         * Example: @haspermission('create-posts') ... @endhaspermission
         */
        Blade::if('haspermission', function ($permission) {
            $user = Auth::user();
            return $user && method_exists($user, 'hasPermission') && $user->hasPermission($permission);
        });

        /**
         * @hasanypermission(['create-posts', 'edit-posts'])
         * 
         * Checks if the authenticated user has any of the specified permissions.
         * Example: @hasanypermission(['create-posts', 'edit-posts']) ... @endhasanypermission
         */
        Blade::if('hasanypermission', function ($permissions) {
            $user = Auth::user();
            return $user && method_exists($user, 'hasAnyPermission') && $user->hasAnyPermission(is_array($permissions) ? $permissions : func_get_args());
        });

        /**
         * @hasallpermissions(['create-posts', 'edit-posts'])
         * 
         * Checks if the authenticated user has all of the specified permissions.
         * Example: @hasallpermissions(['create-posts', 'edit-posts']) ... @endhasallpermissions
         */
        Blade::if('hasallpermissions', function ($permissions) {
            $user = Auth::user();
            return $user && method_exists($user, 'hasAllPermissions') && $user->hasAllPermissions(is_array($permissions) ? $permissions : func_get_args());
        });
    }
}
