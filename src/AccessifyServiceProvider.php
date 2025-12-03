<?php

namespace Abdulbaset\Accessify;

use Illuminate\Support\ServiceProvider;
use Abdulbaset\Accessify\Providers\BladeServiceProvider;

/**
 * AccessifyServiceProvider
 *
 * The main service provider for the Laravel Accessify package.
 * This class handles package registration, configuration publishing, and service bootstrapping.
 * It's responsible for setting up the package's core functionality and making it available
 * to the Laravel application.
 *
 * @package Abdulbaset\Accessify
 * @author Abdulbaset R. Sayed
 * @link https://github.com/AbdulbasetRS/laravel-accessify
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed
 * @version 1.0.0
 * @license MIT
 */
class AccessifyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the main class to use with the facade
        $this->mergeConfigFrom(
            __DIR__.'/../config/accessify.php', 'accessify'
        );

        // Register the Blade service provider
        $this->app->register(BladeServiceProvider::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config file
        $this->publishes([
            __DIR__.'/../config/accessify.php' => config_path('accessify.php'),
        ], 'accessify-config');

        // Load migrations directly from the package
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Optionally allow publishing migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'accessify-migrations');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\RolesSeedCommand::class,
                Console\Commands\RolesSyncCommand::class,
                Console\Commands\PermissionsSeedCommand::class,
                Console\Commands\PermissionsSyncCommand::class,
            ]);
        }
        
        // Register middleware
        $router = $this->app['router'];
        $router->aliasMiddleware('role', \Abdulbaset\Accessify\Http\Middleware\RoleMiddleware::class);
        $router->aliasMiddleware('permission', \Abdulbaset\Accessify\Http\Middleware\PermissionMiddleware::class);
        $router->aliasMiddleware('role_or_permission', \Abdulbaset\Accessify\Http\Middleware\RoleOrPermissionMiddleware::class);
    }
}
