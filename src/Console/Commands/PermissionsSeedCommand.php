<?php

namespace Abdulbaset\Accessify\Console\Commands;

use Illuminate\Console\Command;
use Abdulbaset\Accessify\Models\Permission;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * PermissionsSeedCommand
 *
 * This command seeds the database with permissions from roles configuration.
 * It only adds new permissions and skips existing ones.
 *
 * @package Abdulbaset\Accessify\Console\Commands
 * @author Abdulbaset R. Sayed
 * @link https://github.com/AbdulbasetRS/laravel-accessify
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed
 * @version 1.0.0
 * @license MIT
 */
class PermissionsSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accessify:permissions:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed permissions from config file. Supports flexible permission formats.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $output = new SymfonyStyle($this->input, $this->output);
        
        try {
            $output->text('🌱 Seeding permissions...');
            
            // Get permissions from config
            $permissions = Config::get('accessify.permissions', []);
            
            if (empty($permissions)) {
                $output->warning('No permissions found in configuration. Please check your config/accessify.php file.');
                return Command::FAILURE;
            }
            
            // Process permissions and generate unique slugs
            $processedPermissions = $this->processPermissions($permissions);
            
            $addedCount = 0;
            $foundCount = 0;
            
            foreach ($processedPermissions as $permission) {
                // Check if permission exists
                $permissionModel = Permission::where('slug', $permission['slug'])->first();
                
                if ($permissionModel) {
                    $foundCount++;
                    continue;
                }
                
                // Create new permission
                Permission::create($permission);
                $addedCount++;
            }
            
            $output->newLine();
            $output->success("✅ Permissions seeding completed!");
            $output->text("  - Added: {$addedCount} new permissions");
            $output->text("  - Found: {$foundCount} existing permissions");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $output->error('Error seeding permissions: ' . $e->getMessage());
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
            // Determine if it's key => value OR just a simple string value
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