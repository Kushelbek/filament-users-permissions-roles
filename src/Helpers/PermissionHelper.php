<?

namespace Kushelbek\FilamentUsersPermissionsRoles\Helpers;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PermissionHelper
{
    /**
     * Generate permissions for a resource.
     */
    public static function generateResourcePermissions(string $resource): array
    {
        $actions = ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete'];
        $permissions = [];

        foreach ($actions as $action) {
            $permissions[] = Str::slug($resource, '.') . '.' . $action;
        }

        return $permissions;
    }

    /**
     * Generate wildcard permission for a resource.
     */
    public static function generateWildcardPermission(string $resource): string
    {
        return Str::slug($resource, '.') . '.*';
    }

    /**
     * Sync permissions for a resource.
     */
    public static function syncResourcePermissions(string $resource, string $guard = 'web'): array
    {
        $permissions = self::generateResourcePermissions($resource);
        $created = [];

        foreach ($permissions as $permission) {
            $perm = Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => $guard,
            ]);
            $created[] = $perm->name;
        }

        self::clearCache();

        return $created;
    }

    /**
     * Get all permission groups.
     */
    public static function getPermissionGroups(): array
    {
        return Cache::remember('permission_groups', 3600, function () {
            return Permission::all()->groupBy(function ($permission) {
                $parts = explode('.', $permission->name);
                return $parts[0] ?? 'other';
            })->map(function ($group) {
                return $group->pluck('name');
            })->toArray();
        });
    }

    /**
     * Get permissions by group.
     */
    public static function getPermissionsByGroup(string $group): array
    {
        $groups = self::getPermissionGroups();
        
        return $groups[$group] ?? [];
    }

    /**
     * Check if permission exists.
     */
    public static function permissionExists(string $permission, string $guard = 'web'): bool
    {
        return Permission::where('name', $permission)
            ->where('guard_name', $guard)
            ->exists();
    }

    /**
     * Create permission if not exists.
     */
    public static function createPermission(string $permission, string $guard = 'web'): Permission
    {
        return Permission::firstOrCreate([
            'name' => $permission,
            'guard_name' => $guard,
        ]);
    }

    /**
     * Assign permission to role.
     */
    public static function assignPermissionToRole(string $permission, string $role, string $guard = 'web'): bool
    {
        $permissionModel = self::createPermission($permission, $guard);
        $roleModel = Role::firstOrCreate([
            'name' => $role,
            'guard_name' => $guard,
        ]);

        if (!$roleModel->hasPermissionTo($permissionModel)) {
            $roleModel->givePermissionTo($permissionModel);
            self::clearCache();
            return true;
        }

        return false;
    }

    /**
     * Revoke permission from role.
     */
    public static function revokePermissionFromRole(string $permission, string $role, string $guard = 'web'): bool
    {
        $roleModel = Role::where('name', $role)
            ->where('guard_name', $guard)
            ->first();

        if ($roleModel && $roleModel->hasPermissionTo($permission)) {
            $roleModel->revokePermissionTo($permission);
            self::clearCache();
            return true;
        }

        return false;
    }

    /**
     * Get roles with permission.
     */
    public static function getRolesWithPermission(string $permission, string $guard = 'web'): array
    {
        $permissionModel = Permission::where('name', $permission)
            ->where('guard_name', $guard)
            ->first();

        if (!$permissionModel) {
            return [];
        }

        return $permissionModel->roles->pluck('name')->toArray();
    }

    /**
     * Get users with permission.
     */
    public static function getUsersWithPermission(string $permission, string $guard = 'web'): array
    {
        $permissionModel = Permission::where('name', $permission)
            ->where('guard_name', $guard)
            ->first();

        if (!$permissionModel) {
            return [];
        }

        $users = [];
        $userModel = config('auth.providers.users.model', 'App\\Models\\User');

        if (class_exists($userModel)) {
            $users = $userModel::permission($permission)->get()->pluck('id')->toArray();
        }

        return $users;
    }

    /**
     * Check if permission is assigned to any role.
     */
    public static function isPermissionAssigned(string $permission, string $guard = 'web'): bool
    {
        return Permission::where('name', $permission)
            ->where('guard_name', $guard)
            ->has('roles')
            ->exists();
    }

    /**
     * Get unused permissions.
     */
    public static function getUnusedPermissions(string $guard = 'web'): array
    {
        return Permission::where('guard_name', $guard)
            ->doesntHave('roles')
            ->pluck('name')
            ->toArray();
    }

    /**
     * Validate permission name.
     */
    public static function validatePermissionName(string $permission): bool
    {
        // Формат: resource.action или resource.subresource.action
        $pattern = '/^[a-z0-9]+(?:\.[a-z0-9]+)*\.[a-z0-9]+$/i';
        
        return preg_match($pattern, $permission) === 1;
    }

    /**
     * Extract resource from permission name.
     */
    public static function extractResource(string $permission): string
    {
        $parts = explode('.', $permission);
        return $parts[0] ?? '';
    }

    /**
     * Extract action from permission name.
     */
    public static function extractAction(string $permission): string
    {
        $parts = explode('.', $permission);
        return end($parts) ?: '';
    }

    /**
     * Clear permission cache.
     */
    public static function clearCache(): void
    {
        Cache::forget('permission_groups');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Get permission statistics.
     */
    public static function getStatistics(): array
    {
        return Cache::remember('permission_statistics', 300, function () {
            return [
                'total_permissions' => Permission::count(),
                'total_roles' => Role::count(),
                'permissions_with_roles' => Permission::has('roles')->count(),
                'roles_with_permissions' => Role::has('permissions')->count(),
                'unused_permissions' => Permission::doesntHave('roles')->count(),
                'roles_without_permissions' => Role::doesntHave('permissions')->count(),
            ];
        });
    }

    /**
     * Get permission tree structure.
     */
    public static function getPermissionTree(): array
    {
        $permissions = Permission::all();
        $tree = [];

        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            
            $current = &$tree;
            foreach ($parts as $part) {
                if (!isset($current[$part])) {
                    $current[$part] = [];
                }
                $current = &$current[$part];
            }
        }

        return $tree;
    }
}