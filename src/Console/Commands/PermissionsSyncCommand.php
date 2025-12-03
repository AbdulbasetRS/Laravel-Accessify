<?php

namespace Abdulbaset\Accessify\Console\Commands;

use Illuminate\Console\Command;
use Abdulbaset\Accessify\Models\Permission;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * PermissionsSyncCommand
 *
 * This command synchronizes permissions by deleting all existing permissions
 * and recreating them from the configuration file.
 *
 * @package Abdulbaset\Accessify\Console\Commands
 * @author Abdulbaset R. Sayed
 * @link https://github.com/AbdulbasetRS/laravel-accessify
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed
 * @version 1.0.0
 * @license MIT
 */
class PermissionsSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accessify:permissions:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize permissions with config file. Deletes all permissions and recreates them from config.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $output = new SymfonyStyle($this->input, $this->output);
        
        try {
            $output->text('🌱 Synchronizing permissions...');
            
            // Get count of existing permissions before deletion
            $deletedCount = Permission::count();
            
            // Delete all existing permissions
            Permission::query()->delete();
            
            // Get permissions from config
            $permissions = Config::get('accessify.permissions', []);
            
            if (empty($permissions)) {
                $output->warning('No permissions found in configuration. Please check your config/accessify.php file.');
                return Command::FAILURE;
            }
            
            // Process permissions and generate unique slugs
            $processedPermissions = $this->processPermissions($permissions);
            
            $addedCount = 0;
            
            foreach ($processedPermissions as $permission) {
                // Create new permission
                Permission::create($permission);
                $addedCount++;
            }
            
            $output->newLine();
            $output->success("✅ Permissions synchronization completed!");
            $output->text("  - Deleted: {$deletedCount} permissions");
            $output->text("  - Total: {$addedCount} permissions");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $output->error('Error synchronizing permissions: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Process permissions and generate unique slugs
     *
     * @param array $permissions
     * @return array
     */
    public function processPermissions(array $permissions): array
    {
        $result = [];
        
        foreach ($permissions as $key => $value) {
            if (is_string($value)) {
                $name = ucwords($value);
                $slug = Str::slug($value);
                $description = "Ability to $name" . ' permission.';
            } else {
                $name = ucwords($key);
                $slug = $value['slug'] ?? Str::slug($key);
                $description = $value['description'] ?? "Ability to $name" . ' permission.';
            }
    
            $result[] = [
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
            ];
        }
    
        return $result;
    }
}