<?php

namespace Tests\Feature;

use Abdulbaset\Accessify\Models\Permission;
use Abdulbaset\Accessify\Models\Role;
use Tests\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        
        // Create a test user
        $this->user = new (config('auth.providers.users.model'))([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->user->save();
    }

    /** @test */
    public function it_can_create_a_role()
    {
        $role = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'Admin',
            'slug' => 'admin',
        ]);
    }

    /** @test */
    public function it_can_assign_role_to_user()
    {
        $role = Role::create(['name' => 'admin', 'slug' => 'admin']);
        $user = User::create([
            'name' => 'Test User 1',
            'email' => 'test1@example.com',
            'password' => bcrypt('password'),
        ]);

        $user->role()->attach($role);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertNotNull($user->role);
        $this->assertEquals('admin', $user->role->slug);
    }

    /** @test */
    public function it_can_check_user_permission_through_role()
    {
        $role = Role::create(['name' => 'editor', 'slug' => 'editor']);
        $permission = Permission::create(['name' => 'edit articles', 'slug' => 'edit-articles']);
        
        $role->permissions()->attach($permission);
        
        $user = User::create([
            'name' => 'Test User 1',
            'email' => 'test1@example.com',
            'password' => bcrypt('password'),
        ]);

        $user->role()->attach($role);

        $this->assertTrue($user->hasPermission('edit-articles'));
        $this->assertTrue($user->role->permissions->contains('slug', 'edit-articles'));
    }

    /** @test */
    public function it_can_use_roles_seed_command()
    {
        $this->artisan('accessify:roles:seed')
            ->assertExitCode(0);
            
        $this->assertDatabaseHas(config('accessify.tables.roles'), ['slug' => 'admin']);
        $this->assertDatabaseHas(config('accessify.tables.roles'), ['slug' => 'editor']);
    }

    /** @test */
    public function it_can_use_roles_sync_command()
    {
        $this->artisan('accessify:roles:sync')
            ->assertExitCode(0);
            
        $this->assertDatabaseHas(config('accessify.tables.roles'), ['slug' => 'admin']);
    }
}
