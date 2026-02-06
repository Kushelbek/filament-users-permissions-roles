<?
namespace Kushelbek\FilamentUsersPermissionsRoles\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'filament-users-permissions:install 
                            {--fresh : Run fresh migrations}
                            {--seed : Seed default roles and permissions}
                            {--force : Force the operation}
                            {--no-interaction : Do not ask any interactive questions}';
    
    protected $description = 'Install filament users permissions and roles package';
    
    public function handle(): int
    {
        $this->info('🚀 Installing Kushelbek Filament Users Permissions & Roles...');
        
        // Шаг 1: Проверяем установлен ли Spatie
        $this->checkSpatieInstallation();
        
        // Шаг 2: Публикуем зависимости Spatie
        $this->publishSpatieResources();
        
        // Шаг 3: Публикуем ресурсы пакета
        $this->publishPackageResources();
        
        // Шаг 4: Запускаем миграции
        $this->runMigrations();
        
        // Шаг 5: Сидим данные
        if ($this->option('seed') || $this->confirm('Seed default roles and permissions?', true)) {
            $this->seedDefaultRolesAndPermissions();
        }
        
        // Шаг 6: Публикуем Filament ресурсы если нужно
        $this->publishFilamentResources();
        
        $this->showSuccessMessage();
        
        return self::SUCCESS;
    }
    
    protected function checkSpatieInstallation(): void
    {
        if (!class_exists(\Spatie\Permission\PermissionServiceProvider::class)) {
            $this->error('❌ Spatie Laravel Permission is not installed.');
            
            if ($this->option('no-interaction') || $this->confirm('Install spatie/laravel-permission now?', true)) {
                $this->info('Installing spatie/laravel-permission...');
                
                // Пытаемся установить через composer
                exec('composer require spatie/laravel-permission', $output, $returnCode);
                
                if ($returnCode !== 0) {
                    $this->error('Failed to install spatie/laravel-permission');
                    $this->line('Please install it manually: composer require spatie/laravel-permission');
                    exit(1);
                }
                
                $this->info('✅ spatie/laravel-permission installed successfully.');
            } else {
                $this->line('Please install it manually: composer require spatie/laravel-permission');
                exit(1);
            }
        }
    }
    
    protected function publishSpatieResources(): void
    {
        $this->info('📦 Publishing Spatie resources...');
        
        // Публикуем миграции Spatie если их нет
        $spatieMigrationsExist = count(glob(database_path('migrations/*_create_permission_tables.php'))) > 0;
        
        if (!$spatieMigrationsExist || $this->option('force')) {
            $this->call('vendor:publish', [
                '--provider' => 'Spatie\Permission\PermissionServiceProvider',
                '--tag' => 'migrations',
                '--force' => $this->option('force'),
            ]);
            $this->info('✅ Spatie migrations published.');
        } else {
            $this->info('⏩ Spatie migrations already exist, skipping.');
        }
        
        // Публикуем конфиг Spatie если его нет
        if (!File::exists(config_path('permission.php')) || $this->option('force')) {
            $this->call('vendor:publish', [
                '--provider' => 'Spatie\Permission\PermissionServiceProvider',
                '--tag' => 'config',
                '--force' => $this->option('force'),
            ]);
            $this->info('✅ Spatie config published.');
        } else {
            $this->info('⏩ Spatie config already exists, skipping.');
        }
    }
    
    protected function publishPackageResources(): void
    {
        $this->info('📦 Publishing package resources...');
        
        $tags = ['config', 'migrations', 'translations', 'views'];
        
        foreach ($tags as $tag) {
            $this->call('vendor:publish', [
                '--tag' => "filament-users-permissions-{$tag}",
                '--force' => $this->option('force'),
            ]);
            $this->info("✅ Package {$tag} published.");
        }
    }
    
    protected function runMigrations(): void
    {
        $this->info('🔄 Running migrations...');
        
        if ($this->option('fresh')) {
            $this->call('migrate:fresh');
        } else {
            $this->call('migrate');
        }
        
        $this->info('✅ Migrations completed.');
    }
    
    protected function seedDefaultRolesAndPermissions(): void
    {
        $this->info('🌱 Seeding default roles and permissions...');
        
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
    
    protected function publishFilamentResources(): void
    {
        $this->info('🎨 Publishing Filament resources...');
        
        // Публикуем Filament ресурсы
        $this->call('filament:upgrade');
        
        $this->info('✅ Filament resources published.');
    }
    
    protected function showSuccessMessage(): void
    {
        $this->info('');
        $this->info('🎉 Package installed successfully!');
        $this->info('');
        $this->info('📋 Next steps:');
        $this->line('  1. Add \Kushelbek\FilamentUsersPermissionsRoles\Traits\HasRoleManagement trait to your UserResource');
        $this->line('  2. Customize configuration in config/filament-users-permissions.php');
        $this->line('  3. Run php artisan filament-users-permissions:sync to sync existing permissions');
        $this->line('  4. Clear cache: php artisan optimize:clear');
        $this->info('');
        $this->info('📚 Documentation: https://github.com/kushelbek/filament-users-permissions-roles');
        $this->info('');
    }
}