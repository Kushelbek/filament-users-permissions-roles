<?

namespace Kushelbek\FilamentUsersPermissionsRoles\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionMethod;

class SyncPermissionsCommand extends Command
{
    protected $signature = 'filament-users-permissions:sync
                            {--model= : Sync permissions for specific model}
                            {--guard=web : Guard name}
                            {--force : Override existing permissions}';
    
    protected $description = 'Sync application permissions with database';
    
    public function handle(): int
    {
        $this->info('🔄 Syncing permissions...');
        
        $guard = $this->option('guard');
        $permissions = $this->getApplicationPermissions();
        
        $this->info("Found " . count($permissions) . " permissions to sync");
        
        $created = 0;
        $updated = 0;
        
        foreach ($permissions as $permission) {
            $existing = Permission::where('name', $permission)
                                  ->where('guard_name', $guard)
                                  ->first();
            
            if (!$existing) {
                Permission::create([
                    'name' => $permission,
                    'guard_name' => $guard,
                ]);
                $created++;
                $this->line("  Created: {$permission}");
            } elseif ($this->option('force')) {
                $existing->update(['guard_name' => $guard]);
                $updated++;
                $this->line("  Updated: {$permission}");
            }
        }
        
        $this->info("✅ Sync completed: {$created} created, {$updated} updated");
        
        // Очищаем кэш разрешений
        $this->call('permission:cache-reset');
        
        return self::SUCCESS;
    }
    
    protected function getApplicationPermissions(): array
    {
        $permissions = [];
        
        // Получаем разрешения из конфигурации
        $configPermissions = config('filament-users-permissions.default_permissions', []);
        $permissions = array_merge($permissions, $configPermissions);
        
        // Получаем разрешения из политик (Policies)
        $permissions = array_merge($permissions, $this->getPermissionsFromPolicies());
        
        // Получаем разрешения из Filament ресурсов
        $permissions = array_merge($permissions, $this->getPermissionsFromFilamentResources());
        
        // Уникализируем и сортируем
        $permissions = array_unique($permissions);
        sort($permissions);
        
        return $permissions;
    }
    
    protected function getPermissionsFromPolicies(): array
    {
        $permissions = [];
        $policiesPath = app_path('Policies');
        
        if (!File::exists($policiesPath)) {
            return $permissions;
        }
        
        $policyFiles = File::allFiles($policiesPath);
        
        foreach ($policyFiles as $file) {
            $className = 'App\\Policies\\' . $file->getFilenameWithoutExtension();
            
            if (class_exists($className)) {
                $reflection = new ReflectionClass($className);
                $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
                
                foreach ($methods as $method) {
                    if (in_array($method->getName(), ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete'])) {
                        $modelName = str_replace('Policy', '', $reflection->getShortName());
                        $permission = strtolower($modelName) . '.' . $method->getName();
                        $permissions[] = $permission;
                    }
                }
            }
        }
        
        return $permissions;
    }
    
    protected function getPermissionsFromFilamentResources(): array
    {
        $permissions = [];
        $resourcesPath = app_path('Filament/Resources');
        
        if (!File::exists($resourcesPath)) {
            return $permissions;
        }
        
        $resourceFiles = File::allFiles($resourcesPath);
        
        foreach ($resourceFiles as $file) {
            $resourceName = $file->getFilenameWithoutExtension();
            $permissions = array_merge($permissions, [
                strtolower($resourceName) . '.viewAny',
                strtolower($resourceName) . '.view',
                strtolower($resourceName) . '.create',
                strtolower($resourceName) . '.update',
                strtolower($resourceName) . '.delete',
                strtolower($resourceName) . '.restore',
                strtolower($resourceName) . '.forceDelete',
            ]);
        }
        
        return $permissions;
    }
}