<?php

namespace Kushelbek\FilamentUsersPermissionsRoles\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    protected $signature = 'filament-users-permissions:install 
                            {--seed : Seed default roles and permissions}
                            {--force : Force publish package resources}';
    
    protected $description = 'Install filament users permissions and roles package';
    
    public function handle(): int
    {
        $this->info('🚀 Installing Kushelbek Filament Users Permissions & Roles...');
        
        // Шаг 1: Проверяем, установлен ли Spatie Laravel Permission
        if (!$this->checkSpatieInstalled()) {
            return self::FAILURE;
        }
        
        // Шаг 2: Проверяем, существует ли таблица roles
        if (!$this->checkRolesTableExists()) {
            return self::FAILURE;
        }
        
        // Шаг 3: Публикуем ресурсы нашего пакета (конфиг, миграции, переводы)
        $this->publishPackageResources();
        
        // Шаг 4: Запускаем наши миграции (дополнительные поля, teams)
        $this->runPackageMigrations();
        
        // Шаг 5: Сидирование
        if ($this->option('seed')) {
            $this->seedDefaultRolesAndPermissions();
        }
        
        $this->showSuccessMessage();
        
        return self::SUCCESS;
    }
    
    protected function checkSpatieInstalled(): bool
    {
        if (class_exists(\Spatie\Permission\PermissionServiceProvider::class)) {
            $this->info('✅ Spatie Laravel Permission is installed.');
            return true;
        }
        
        $this->error('❌ Spatie Laravel Permission is NOT installed.');
        $this->line('Please install it first:');
        $this->line('  composer require spatie/laravel-permission');
        $this->newLine();
        $this->line('After that, run this command again.');
        
        return false;
    }
    
    protected function checkRolesTableExists(): bool
    {
        if (Schema::hasTable('roles')) {
            $this->info('✅ Table "roles" exists.');
            return true;
        }
        
        $this->error('❌ Table "roles" does not exist.');
        $this->line('Please publish and run Spatie migrations first:');
        $this->line('  1. php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag=migrations');
        $this->line('  2. php artisan migrate');
        $this->newLine();
        $this->line('Then run this command again.');
        
        return false;
    }
    
    protected function publishPackageResources(): void
    {
        $this->info('📦 Publishing package resources...');
        
        // Публикуем конфиг
        $this->call('vendor:publish', [
            '--tag' => 'filament-users-permissions-config',
            '--force' => $this->option('force'),
        ]);
        
        // Публикуем миграции
        $this->call('vendor:publish', [
            '--tag' => 'filament-users-permissions-migrations',
            '--force' => $this->option('force'),
        ]);
        
        // Публикуем переводы
        $this->call('vendor:publish', [
            '--tag' => 'filament-users-permissions-translations',
            '--force' => $this->option('force'),
        ]);
        
        $this->info('✅ Package resources published.');
    }
    
    protected function runPackageMigrations(): void
    {
        $this->info('🔄 Running package migrations...');
        
        // Запускаем только миграции, добавленные нашим пакетом
        // (они уже опубликованы в database/migrations)
        $this->call('migrate');
        
        $this->info('✅ Package migrations completed.');
    }
    
    protected function seedDefaultRolesAndPermissions(): void
    {
        $this->info('🌱 Seeding default roles and permissions...');
        
        if (!Schema::hasTable('roles')) {
            $this->error('❌ Table "roles" does not exist. Cannot seed.');
            return;
        }
        
        $config = config('filament-users-permissions.default_roles', []);
        
        foreach ($config as $roleConfig) {
            $role = Role::firstOrCreate([
                'name' => $roleConfig['name'],
                'guard_name' => $roleConfig['guard_name'] ?? 'web',
            ]);
            
            if (isset($roleConfig['permissions'])) {
                foreach ($roleConfig['permissions'] as $permissionName) {
                    $permission = Permission::firstOrCreate([
                        'name' => $permissionName,
                        'guard_name' => $roleConfig['guard_name'] ?? 'web',
                    ]);
                    
                    if (!$role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                    }
                }
            }
            
            $this->info("   Created role: {$role->name} with " . count($roleConfig['permissions'] ?? []) . " permissions");
        }
        
        $this->info('✅ Default roles and permissions created.');
    }
    
    protected function showSuccessMessage(): void
    {
        $this->newLine();
        $this->info('🎉 Package installed successfully!');
        $this->newLine();
        $this->info('📋 Next steps:');
        $this->line('  1. Add \\Kushelbek\\FilamentUsersPermissionsRoles\\Traits\\HasRoleManagement trait to your UserResource');
        $this->line('  2. Customize configuration in config/filament-users-permissions.php');
        $this->line('  3. Run php artisan filament-users-permissions:sync to sync existing permissions');
        $this->newLine();
    }
}