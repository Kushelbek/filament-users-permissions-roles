<?

namespace Kushelbek\FilamentUsersPermissionsRoles;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Cache;

class FilamentUsersPermissions
{
    public function syncPermissions(): array
    {
        $permissions = config('filament-users-permissions.default_permissions', []);
        $created = [];
        
        foreach ($permissions as $permission) {
            $perm = Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
            $created[] = $perm->name;
        }
        
        $this->clearCache();
        
        return $created;
    }
    
    public function getPermissionGroups(): array
    {
        return Cache::remember('permission.groups', 3600, function () {
            $permissions = Permission::all()->groupBy(function ($permission) {
                return explode('.', $permission->name)[0] ?? 'other';
            });
            
            return $permissions->map(function ($group) {
                return $group->pluck('name');
            })->toArray();
        });
    }
    
    public function getRoleStats(): array
    {
        return Cache::remember('role.stats', 3600, function () {
            return [
                'total_roles' => Role::count(),
                'total_permissions' => Permission::count(),
                'roles_with_permissions' => Role::has('permissions')->count(),
                'unused_permissions' => Permission::doesntHave('roles')->count(),
            ];
        });
    }
    
    public function clearCache(): void
    {
        Cache::forget('permission.groups');
        Cache::forget('role.stats');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
    
    public function getUserPermissions($user): array
    {
        if (!$user) {
            return [];
        }
        
        return $user->getAllPermissions()->pluck('name')->toArray();
    }
    
    public function checkAccess($user, string $permission): bool
    {
        if (!$user) {
            return false;
        }
        
        return $user->hasPermissionTo($permission);
    }
}