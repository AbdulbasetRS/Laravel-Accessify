# Laravel Accessify

<p align="center">
    <img src="docs/thumbnail.png" alt="Laravel Accessify">
</p>

A simple and flexible package for handling roles and permissions in Laravel applications.

## Why Accessify?

### The Name

The name **Accessify** is derived from the word "Access", which reflects the core concept of the package — controlling access to system resources through roles and permissions. The suffix "-ify" gives the name a modern and dynamic feel, indicating that the package helps you apply access control rules in a flexible and efficient way.

### The Concept

Just like a security guard who determines who can enter a building and what areas they can access, Accessify helps you manage who can do what in your Laravel application — using a simple and expressive Role-Based Access Control (RBAC) system.

In short: **Accessify = "Control access with simplicity and power"**.

### Why Choose Accessify?

- **Simplicity**: Easy to set up and use with minimal configuration
- **Flexibility**: Adapts to your application's needs with customizable roles and permissions
- **Performance**: Lightweight and optimized for speed
- **Laravel Integration**: Built specifically for Laravel with best practices in mind
- **Active Maintenance**: Regularly updated and maintained

## Requirements

- PHP 8.1 or higher
- Laravel 10.x, 11.x, or 12.x

## Features

- Role-based access control (RBAC)
- Permission management
- Blade directives for easy role and permission checks in views
- Artisan commands for managing roles and permissions
- Support for multiple roles per user
- Lightweight and easy to integrate

## Installation

1. Install the package via Composer:

```bash
composer require abdulbaset/laravel-accessify
```

2. Publish the configuration file (optional):

```bash
php artisan vendor:publish --provider="Abdulbaset\Accessify\AccessifyServiceProvider" --tag="accessify-config"
```

3. Run the migrations:

```bash
php artisan migrate
```

> **Note:** The migrations will run automatically from the package. If you need to modify them, you can publish them using:
>
> ```bash
> php artisan vendor:publish --provider="Abdulbaset\Accessify\AccessifyServiceProvider" --tag="accessify-migrations"
> ```

## Configuration

You can customize the package by publishing the configuration file:

```bash
php artisan vendor:publish --provider="Abdulbaset\Accessify\AccessifyServiceProvider" --tag="accessify-config"
```

## Artisan Commands

### 1. Managing Roles

#### Seed Roles (Safe ✅)

```bash
php artisan accessify:roles:seed
```

This command will **safely seed** roles from your config file. It will:

- Create any new roles from your config file
- Create missing permissions for roles
- Add missing permissions to roles
- **Never delete** any roles, even if they're not in your config file

#### Sync Roles (Dangerous ⚠️)

```bash
php artisan accessify:roles:sync
```

This command will **synchronize** roles between your config file and database. It will:

- **Delete** any roles not in your config file
- Create all roles from your config file
- Create missing permissions for roles
- Add missing permissions to roles

### 2. Managing Permissions

#### Seed Permissions (Safe ✅)

```bash
php artisan accessify:permissions:seed
```

This command will **safely seed** permissions from your config file. It will:

- Create any new permissions from your config file
- **Never delete** any permissions, even if they're not in your config file

#### Sync Permissions (Dangerous ⚠️)

```bash
php artisan accessify:permissions:sync
```

This command will **synchronize** permissions between your config file and database. It will:

- **Delete** any permissions not in your config file
- Create all permissions from your config file

### When to Use Each Command

| Command                      | Safe?        | Best For                                                                 |
| ---------------------------- | ------------ | ------------------------------------------------------------------------ |
| `accessify:roles:seed`       | ✅ Safe      | Initial setup or adding new roles without affecting existing ones        |
| `accessify:roles:sync`       | ⚠️ Dangerous | Cleaning up old roles and ensuring database matches config exactly       |
| `accessify:permissions:seed` | ✅ Safe      | Adding new permissions without affecting existing ones                   |
| `accessify:permissions:sync` | ⚠️ Dangerous | Cleaning up old permissions and ensuring database matches config exactly |

### Recommended Workflow

1. Use the safe `accessify:roles:seed` and `accessify:permissions:seed` for normal development
2. Only use `accessify:roles:sync` and `accessify:permissions:sync` when you need to clean up old data
3. Always backup your database before running sync commands
4. In production, consider running sync commands in a controlled manner after thorough testing

## Usage

### Add HasRoles Trait to User Model

Add the `HasRoles` trait to your User model:

```php
use Abdulbaset\Accessify\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasRoles;

    // ...
}
```

> **Important:** Each user can have only one role. The system enforces this constraint at the database level.

### Built-in Middleware

This package includes three middleware classes that are automatically registered with the service provider. These provide out-of-the-box protection for your routes.

#### Available Middleware

1. **Role Middleware** - Restrict access to users with a specific role
2. **Permission Middleware** - Restrict access to users with a specific permission
3. **Role or Permission Middleware** - Restrict access to users with either a specific role or permission

#### Usage in Routes

Use the built-in middleware directly in your route definitions like this:

```php
// Using role middleware
Route::get('/admin', function () {
    // Users with either 'admin' or 'super-admin' role can access this route
})->middleware('role:admin|super-admin');

Route::get('/posts', function () {
    // Users with either 'view-posts' or 'manage-posts' permission can access this route
})->middleware('permission:view-posts|manage-posts');

Route::get('/dashboard', function () {
    // Users with either 'admin' role or 'view-dashboard' permission can access this route
})->middleware('role_or_permission:admin|view-dashboard');
```

#### Usage in Controllers

You can also apply middleware in your controller's constructor:

1. Laravel 10.x ([Source: Laravel 10.x Documentation](https://laravel.com/docs/10.x/controllers#controller-middleware))

```php
class YourController extends Controller
{
    public function __construct()
    {
        // Authentication middleware
        $this->middleware('auth');

        // Role middleware with specific methods
        $this->middleware('role:admin')->only('index');

        // Or for multiple roles
        $this->middleware('role:admin|editor')->only('index');

        // Using permission middleware
        $this->middleware('permission:edit-posts')->only('store');

        // Or for multiple permissions
        $this->middleware('permission:edit-posts|store-posts')->only('store');

        // Using role or permission middleware
        $this->middleware('role_or_permission:admin|edit-posts')->only('store');
    }
}
```

2. Laravel 11.x ([Source: Laravel 11.x Documentation](https://laravel.com/docs/11.x/controllers#controller-middleware))

```php
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class YourController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            // Authentication middleware
            'auth',

            // Role middleware with specific methods
            new Middleware('role:admin', ['index', 'show']),

            // Multiple roles with OR condition
            new Middleware('role:admin|editor', ['create', 'store']),

            // Permission middleware with specific methods
            new Middleware('permission:create', ['store']),

            // Multiple permissions with OR condition
            new Middleware('permission:create|update|delete', ['destroy']),

            // Role or permission middleware with multiple methods
            new Middleware('role_or_permission:admin|edit-posts', ['edit', 'update']),
        ];
    }
}
```

3. Laravel 12.x ([Source: Laravel 12.x Documentation](https://laravel.com/docs/12.x/controllers#controller-middleware))

```php
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class YourController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            // Authentication middleware
            'auth',

            // Role middleware with specific methods
            new Middleware('role:admin', ['index', 'show']),

            // Multiple roles with OR condition
            new Middleware('role:admin|editor', ['create', 'store']),

            // Permission middleware with specific methods
            new Middleware('permission:create', ['store']),

            // Multiple permissions with OR condition
            new Middleware('permission:create|update|delete', ['destroy']),

            // Role or permission middleware with multiple methods
            new Middleware('role_or_permission:admin|edit-posts', ['edit', 'update']),
        ];
    }
}
```

#### Multiple Roles/Permissions

You can specify multiple roles or permissions by separating them with a comma:

```php
// User must have at least one of the specified roles
Route::get('/admin', function () {
    // User must have EITHER 'admin' OR 'super-admin' role
})->middleware('role:admin|super-admin');

// User must have at least one of the specified permissions
Route::get('/posts', function () {
    // User must have EITHER 'view-posts' OR 'manage-posts' permission
})->middleware('permission:view-posts|manage-posts');
```

#### Middleware Groups

You can also use these middleware in route groups:

```php
// All routes in this group require the 'admin' role
Route::middleware(['role:admin'])->group(function () {
    Route::get('/admin/dashboard', 'AdminController@dashboard');
    Route::get('/admin/users', 'AdminController@users');
});

// All routes in this group require the 'edit-posts' permission
Route::middleware(['permission:edit-posts'])->group(function () {
    Route::get('/posts/create', 'PostController@create');
    Route::post('/posts', 'PostController@store');
    Route::get('/posts/{post}/edit', 'PostController@edit');
    Route::put('/posts/{post}', 'PostController@update');
});
```

### Available Methods

#### User Model Methods

```php
// Get the user's role
$role = $user->role;

// Check if user has a specific role
$user->hasRole('admin');

// Check if user has any of the specified roles
$user->hasAnyRole(['admin', 'editor']);

// Check if user has a permission
$user->hasPermission('edit-posts');

// Check if user has any of the given permissions
$user->hasAnyPermission(['edit-posts', 'delete-posts']);

// Check if user has all of the given permissions
$user->hasAllPermissions(['edit-posts', 'delete-posts']);

// Assign a role to a user (replaces any existing role)
$user->giveRole('editor');

// Sync role (alias for giveRole)
$user->syncRoles('editor');

// Remove the user's role
$user->removeRole();
```

#### Role Model Methods

```php
// Create a new role with description
$role = Role::create([
    'name' => 'Editor',
    'slug' => 'editor',
    'description' => 'Can edit and manage content'
]);

// Update role description
$role->update(['description' => 'Updated role description']);

// Get role description
$description = $role->description;
```

#### Permission Model Methods

```php
// Create a new permission with description
$editPosts = Permission::create([
    'name' => 'Edit Posts',
    'slug' => 'edit-posts',
    'description' => 'Allows editing of existing posts'
]);

// Update permission description
$permission->update(['description' => 'Updated permission description']);

// Get permission description
$description = $permission->description;
```

##### Example Usage:

```php
// Assign a role to a user
$user->giveRole('admin');

// Check user's role
if ($user->hasRole('admin')) {
    // User is an admin
}

// Get the role name
$roleName = $user->role ? $user->role->name : 'No role';

// Remove role
$user->removeRole();

// Check if user has any of these roles
if ($user->hasAnyRole(['admin', 'moderator'])) {
    // User is either admin or moderator
}
```

#### Role Model Methods

```php
// Get a role
$role = Role::where('slug', 'admin')->first();

// Check if role has a permission
$role->hasPermission('edit-posts');

// Give permission to a role
$role->givePermission('edit-posts');

// Assign multiple permissions
$role->givePermissions(['edit-posts', 'delete-posts']);

// Check if role has any permission
$role->hasAnyPermission(['edit-posts', 'delete-posts']);

// Sync all permissions for a role (removes all existing permissions and adds the given ones)
$role->syncPermissions(['edit-posts', 'delete-posts']);

// Remove a specific permission from a role
$role->removePermission('edit-posts');

// Remove multiple permissions from a role
$removedCount = $role->removePermissions(['edit-posts', 'delete-posts']);  // Returns number of permissions removed

// Remove all permissions from a role
$removedCount = $role->removeAllPermissions();  // Returns number of permissions removed
```

## Permissions Methods

### Using getPermissions()

```php
// Get all permissions for a role
$rolePermissions = $role->getPermissions();

// Get all permissions for a user's role
$userPermissions = $user->getPermissions();

// Example: Loop through permissions
foreach ($user->getPermissions() as $permission) {
    echo $permission->name; // e.g., 'edit-posts'
    echo $permission->description; // e.g., 'Can edit posts'
}

// Check if user has any permissions
if ($user->getPermissions()->isNotEmpty()) {
    // User has permissions
}

// Check if role has any permissions
if ($role->getPermissions()->isNotEmpty()) {
    // Role has permissions
}
```

## Authorization Methods

### 1. Using FormRequest

You can check permissions directly in your FormRequest classes:

```php
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->hasPermission('create-product');
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ];
    }
}
```

### 2. Using Policies

1. First, create a policy:

```bash
php artisan make:policy ProductPolicy --model=Product
```

2. Implement the policy methods:

```php
namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasPermission('view-products');
    }

    public function view(User $user, Product $product)
    {
        return $user->hasPermission('view-product');
    }

    public function create(User $user)
    {
        return $user->hasPermission('create-product');
    }

    // ... other methods
}
```

3. Register the policy in `AuthServiceProvider`:

```php
protected $policies = [
    Product::class => ProductPolicy::class,
];
```

### 3. Using Custom Middleware

If you need more complex authorization logic, you can create custom middleware. Here's an example of creating a custom permission middleware:

1. Create the middleware:

```bash
php artisan make:middleware CustomPermission
```

2. Implement the middleware:

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomPermission
{
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!auth()->check()) {
            abort(403, 'Unauthenticated.');
        }

        foreach ($permissions as $permission) {
            if (auth()->user()->hasPermission($permission)) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized action.');
    }
}
```

3. Register the middleware:

#### For Laravel 10

Register in `app/Http/Kernel.php`:

```php
// app/Http/Kernel.php

protected $routeMiddleware = [
    // ... other middleware
    'custom.permission' => \App\Http\Middleware\CustomPermission::class,
];
```

#### For Laravel 11 & 12

Register in `bootstrap/app.php`:

```php
// bootstrap/app.php

use App\Http\Middleware\CustomPermission;

// ... other code

->withMiddleware(function (Middleware $middleware) {
    // ... other middleware registrations
    $middleware->alias([
        'custom.permission' => CustomPermission::class,
    ]);
});
```

4. Use the custom middleware in your routes:

```php
Route::middleware(['custom.permission:edit-posts'])->group(function () {
    Route::get('/posts/{post}/edit', 'PostController@edit');
    Route::put('/posts/{post}', 'PostController@update');
});
```

This approach gives you the flexibility to:

- Extend or create custom middleware for complex authorization logic
- Maintain clear separation between package features and custom code

### 4. Directly in Controller

You can also check permissions directly in your controller methods:

```php
public function store(Request $request)
{
    // Check permission
    if (!auth()->user()->hasPermission('create-product')) {
        abort(403, 'Unauthorized action.');
    }

    // Validation
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
    ]);

    // Create the product
    $product = Product::create($validated);

    return response()->json($product, 201);
}
```

## Blade Directives

```blade
@role('admin')
    // This content will be shown only to users with the 'admin' role
@endrole

@hasanyrole(['admin', 'editor'])
    // This content will be shown to users with either 'admin' or 'editor' role
@endhasanyrole

@haspermission('edit-posts')
    // This content will be shown only to users with the 'edit-posts' permission
@endhaspermission

@hasanypermission(['edit-posts', 'delete-posts'])
    // This content will be shown to users with either 'edit-posts' or 'delete-posts' permission
@endhasanypermission

@hasallpermissions(['edit-posts', 'delete-posts'])
    // This content will be shown only to users with both 'edit-posts' and 'delete-posts' permissions
@endhasallpermissions
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover any security related issues, please email AbdulbasetRedaSayedHF@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
