<?php

namespace Abdulbaset\Accessify\Console\Commands;

use Illuminate\Console\Command;
use Abdulbaset\Accessify\Models\Role;
use Abdulbaset\Accessify\Models\Permission;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * RolesSyncCommand
 *
 * This Artisan command synchronizes roles between your configuration file
 * and the database. It ensures that all roles defined in your roles configuration
 * exist in the database, and removes any roles that are no longer in use.
 *
 * @package Abdulbaset\Accessify\Console\Commands
 * @author Abdulbaset R. Sayed
 * @link https://github.com/AbdulbasetRS/laravel-accessify
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed
 * @version 1.0.0
 * @license MIT
 */
class RolesSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accessify:roles:sync';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $output = new SymfonyStyle($this->input, $this->output);
        
        try {
            $output->text('🔄 Syncing roles and permissions...');
            
            // Get roles and permissions from config
            $roles = Config::get('accessify.roles', []);
            
            if (empty($roles)) {
                $output->warning('No roles found in configuration. Please check your config/accessify.php file.');
                return Command::FAILURE;
            }
            
            // Count before deletion
            $deletedRolesCount = Role::count();
            
            // Delete all existing roles (this will also delete role_permission relationships due to cascade)
            Role::query()->delete();
            $output->text("🗑️  Deleted <comment>{$deletedRolesCount}</comment> existing roles");
            
            // Get all unique permissions
            $uniquePermissions = $this->getUniquePermissionsFromRoles();
            
            // Count existing permissions before adding new ones
            $existingPermissionsCount = Permission::count();
            
            // Add new permissions if they don't exist
            $addedPermissionsCount = 0;
            foreach ($uniquePermissions as $slug => $permissionData) {
                $permission = Permission::firstOrNew(['slug' => $slug]);
                
                if (!$permission->exists) {
                    $permission->name = $permissionData['name'];
                    $permission->description = $permissionData['description'] ?? ucfirst(str_replace(['-', '_'], ' ', $slug));
                    $permission->save();
                    $addedPermissionsCount++;
                }
            }
            
            // Create roles and attach permissions
            $addedRolesCount = 0;
            foreach ($roles as $slug => $roleData) {
                $role = new Role();
                $role->slug = $slug;
                $role->name = $roleData['name'] ?? ucfirst(str_replace(['-', '_'], ' ', $slug));
                $role->save();
                $addedRolesCount++;
                
                // Attach permissions to role
                if (isset($roleData['permissions']) && is_array($roleData['permissions'])) {
                    $permissionIds = [];
                    
                    foreach ($roleData['permissions'] as $permission) {
                        $permissionData = is_string($permission)
                            ? ['name' => $permission]
                            : $permission;
                            
                        $permission = Permission::where('slug', $permissionData['name'])->first();
                        if ($permission) {
                            $permissionIds[] = $permission->id;
                        }
                    }
                    
                    if (!empty($permissionIds)) {
                        $role->permissions()->sync($permissionIds);
                    }
                }
            }
            
            // Prepare and display results
            $output->newLine();
            $output->success('✅ Sync completed!');
            
            // Display roles summary
            $output->text('  - Roles:');
            $output->text(sprintf('    • Deleted: <comment>%d</comment>', $deletedRolesCount));
            $output->text(sprintf('    • Added: <info>%d</info>', $addedRolesCount));
            
            // Display permissions summary
            $output->text('  - Permissions:');
            $output->text(sprintf('    • Existing: <comment>%d</comment>', $existingPermissionsCount));
            $output->text(sprintf('    • Added: <info>%d</info>', $addedPermissionsCount));
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $output->error('Error syncing roles and permissions: ' . $e->getMessage());
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
