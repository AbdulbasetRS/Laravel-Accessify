<?php

namespace Abdulbaset\Accessify\Console\Commands;

use Illuminate\Console\Command;
use Abdulbaset\Accessify\Models\Role;
use Abdulbaset\Accessify\Models\Permission;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * RolesSeedCommand
 *
 * This command seeds roles and their permissions from the configuration file.
 * It only adds new roles and permissions, and updates their relationships.
 *
 * @package Abdulbaset\Accessify\Console\Commands
 * @author Abdulbaset R. Sayed
 * @link https://github.com/AbdulbasetRS/laravel-accessify
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed
 * @version 1.0.0
 * @license MIT
 */
class RolesSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accessify:roles:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed roles and their permissions from configuration. Only adds new roles and permissions.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $output = new SymfonyStyle($this->input, $this->output);
        
        try {
            $output->text('🌱 Seeding roles and permissions...');
            
            // Get roles and permissions from config
            $roles = Config::get('accessify.roles', []);
            
            if (empty($roles)) {
                $output->warning('No roles found in configuration. Please check your config/accessify.php file.');
                return Command::FAILURE;
            }
            
            // Get existing permissions from database
            $existingPermissions = Permission::pluck('slug')->toArray();
            
            $roleStats = [
                'existing' => 0,
                'added' => 0,
            ];
            
            $permissionStats = [
                'existing' => count($existingPermissions),
                'added' => 0,
            ];
            
            // Get all unique permissions first
            $uniquePermissions = $this->getUniquePermissionsFromRoles();
            
            // Create new permissions
            foreach ($uniquePermissions as $slug => $permissionData) {
                if (!in_array($slug, $existingPermissions)) {
                    Permission::create([
                        'slug' => $slug,
                        'name' => $permissionData['name'],
                        'description' => $permissionData['description'] ?? ucfirst(str_replace(['-', '_'], ' ', $slug)),
                    ]);
                    $permissionStats['added']++;
                }
            }
            
            // Create roles and attach permissions
            foreach ($roles as $slug => $roleData) {
                $role = Role::firstOrNew(['slug' => $slug]);
                
                if ($role->exists) {
                    $roleStats['existing']++;
                } else {
                    $role->name = $roleData['name'] ?? ucfirst(str_replace(['-', '_'], ' ', $slug));
                    $role->save();
                    $roleStats['added']++;
                }
                
                // Attach permissions to role
                if (isset($roleData['permissions']) && is_array($roleData['permissions'])) {
                    foreach ($roleData['permissions'] as $permission) {
                        $permissionData = is_string($permission)
                            ? ['name' => $permission]
                            : $permission;
                            
                        $permissionSlug = $permissionData['name'];
                        
                        if (!$role->permissions()->where('slug', $permissionSlug)->exists()) {
                            $role->permissions()->attach(Permission::where('slug', $permissionSlug)->first());
                        }
                    }
                }
            }
            
            $output->newLine();
            $output->success("✅ Roles and permissions seeding completed!");
            $output->text("  - Roles:");
            $output->text("    • Existing: {$roleStats['existing']}");
            $output->text("    • Added: {$roleStats['added']}");
            $output->text("  - Permissions:");
            $output->text("    • Existing: {$permissionStats['existing']}");
            $output->text("    • Added: {$permissionStats['added']}");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $output->error('Error seeding roles and permissions: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Get all unique permissions from roles configuration
     *
     * @return array
     */
    protected function getUniquePermissionsFromRoles(): array
    {
        $permissions = [];
        $roles = Config::get('accessify.roles', []);

        foreach ($roles as $role) {
            if (isset($role['permissions']) && is_array($role['permissions'])) {
                foreach ($role['permissions'] as $permission) {
                    $permissionData = is_string($permission)
                        ? ['name' => $permission]
                        : $permission;

                    if (isset($permissions[$permissionData['name']])) {
                        continue;
                    }

                    $permissions[$permissionData['name']] = [
                        'name' => $permissionData['name'],
                        'description' => $permissionData['description'] ?? ucfirst(str_replace(['-', '_'], ' ', $permissionData['name'])),
                    ];
                }
            }
        }

        return $permissions;
    }
}
