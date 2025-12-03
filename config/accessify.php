<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | This is the User model used by the package. Update this if you're using
    | a custom user model.
    |
    */
    'user_model' => \App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Default Permissions
    |--------------------------------------------------------------------------
    |
    | This array defines all available permissions in the system.
    | Each permission can be defined in three ways:
    | 1. Simple string: 'permission-name'
    | 2. String with array: 'permission-name' => ['description' => '...']
    | 3. String with array: 'permission-name' => ['slug' => 'custom-slug', 'description' => '...']
    |
    */
    'permissions' => [
        'create' => [
            'slug' => 'create',
            'description' => 'Ability to create new record',
        ],
        'read' => [
            'slug' => 'read',
            'description' => 'Ability to read record',
        ],
        'update' => [
            'slug' => 'update',
            'description' => 'Ability to update record',
        ],
        'delete' => [
            'slug' => 'delete',
            'description' => 'Ability to delete record',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Roles and Their Permissions
    |--------------------------------------------------------------------------
    |
    | This array defines the default roles and their associated permissions.
    | Each role should have a unique slug, name, and array of permission slugs.
    |
    */
    'roles' => [
        'admin' => [
            'name' => 'Administrator',
            'permissions' => [
                'create',
                'read',
                'update',
                'delete',
            ],
        ],
        'editor' => [
            'name' => 'Editor',
            'permissions' => [
                'create',
                'read',
                'update',
            ],
        ],
        'viewer' => [
            'name' => 'Viewer',
            'permissions' => [
                'read',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tables
    |--------------------------------------------------------------------------
    |
    | These are the table names used by the package. You can change these
    | table names if you want to customize them.
    |
    */
    'tables' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'role_user' => 'role_user',
        'permission_role' => 'permission_role',
    ],
];
