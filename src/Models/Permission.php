<?php

namespace Abdulbaset\Accessify\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Config;

/**
 * Permission Model
 *
 * Represents a permission in the role-based access control system.
 * Permissions define specific actions that can be performed in the application.
 * They are grouped under roles, and users gain permissions through their assigned role.
 *
 * @package Abdulbaset\Accessify\Models
 * @author Abdulbaset R. Sayed
 * @link https://github.com/AbdulbasetRS/laravel-accessify
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed
 * @version 1.0.0
 * @license MIT
 */
class Permission extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table;

    /**
     * Create a new model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('accessify.tables.permissions');
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',  // The display name of the permission (e.g., "Create Posts")
        'slug',  // URL-friendly version of the name (e.g., "create-posts")
        'description', // Description of what this permission allows
    ];
    
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'pivot'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'slug' => 'string',
        'description' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The roles that belong to the permission.
     * Defines a many-to-many relationship with the Role model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            Config::get('accessify.tables.permission_role'),
            'permission_id',
            'role_id'
        );
    }
}
