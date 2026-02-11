<?

namespace Kushelbek\FilamentUsersPermissionsRoles\Traits;

use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Builder;

trait HasRolesAndPermissions
{
    use HasRoles, HasPermissions;

    /**
     * Scope a query to only include users with a specific role.
     */
    public function scopeRole(Builder $query, $roles, $guard = null): Builder
    {
        if ($roles instanceof Collection) {
            $roles = $roles->all();
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $roles = array_map(function ($role) use ($guard) {
            if ($role instanceof \Spatie\Permission\Models\Role) {
                return $role;
            }

            $method = is_numeric($role) ? 'findById' : 'findByName';
            $guard = $guard ?: $this->getDefaultGuardName();

            return $this->getRoleClass()->{$method}($role, $guard);
        }, $roles);

        return $query->whereHas('roles', function ($query) use ($roles) {
            $query->whereIn(config('permission.table_names.roles').'.id', \array_column($roles, 'id'));
        });
    }

    /**
     * Scope a query to only include users with any of the given roles.
     */
    public function scopeAnyRole(Builder $query, $roles, $guard = null): Builder
    {
        if ($roles instanceof Collection) {
            $roles = $roles->all();
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        return $query->whereHas('roles', function ($q) use ($roles, $guard) {
            $roleClass = $this->getRoleClass();
            $q->whereIn('name', $roles);
            
            if ($guard) {
                $q->where('guard_name', $guard);
            }
        });
    }

    /**
     * Scope a query to only include users with a specific permission.
     */
    public function scopePermission(Builder $query, $permissions): Builder
    {
        $permissions = $this->convertToPermissionModels($permissions);

        $rolesWithPermissions = array_unique(array_reduce($permissions, function ($result, $permission) {
            return array_merge($result, $permission->roles->all());
        }, []));

        return $query->where(function ($query) use ($permissions, $rolesWithPermissions) {
            $query->whereHas('permissions', function ($query) use ($permissions) {
                $query->whereIn(config('permission.table_names.permissions').'.id', \array_column($permissions, 'id'));
            });
            
            if (count($rolesWithPermissions) > 0) {
                $query->orWhereHas('roles', function ($query) use ($rolesWithPermissions) {
                    $query->whereIn(config('permission.table_names.roles').'.id', \array_column($rolesWithPermissions, 'id'));
                });
            }
        });
    }

    /**
     * Check if user has all of the given permissions.
     */
    public function hasAllPermissions(...$permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermissionTo($permission)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(...$permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermissionTo($permission)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(config('filament-users-permissions.super_admin_role', 'super-admin'));
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(config('filament-users-permissions.admin_role', 'admin'));
    }

    /**
     * Get all user permissions including role permissions.
     */
    public function getAllPermissionsNames(): array
    {
        return $this->getAllPermissions()->pluck('name')->toArray();
    }

    /**
     * Get direct user permissions (excluding role permissions).
     */
    public function getDirectPermissionsNames(): array
    {
        return $this->permissions->pluck('name')->toArray();
    }

    /**
     * Get user role permissions.
     */
    public function getRolePermissionsNames(): array
    {
        return $this->getPermissionsViaRoles()->pluck('name')->toArray();
    }

    /**
     * Assign multiple roles to user.
     */
    public function assignRoles(array $roles): self
    {
        $this->syncRoles($roles);
        return $this;
    }

    /**
     * Assign multiple permissions to user.
     */
    public function assignPermissions(array $permissions): self
    {
        $this->syncPermissions($permissions);
        return $this;
    }

    /**
     * Get user permission tree.
     */
    public function getPermissionTree(): array
    {
        $permissions = $this->getAllPermissions();
        $tree = [];

        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $resource = $parts[0] ?? 'other';
            $action = $parts[1] ?? $permission->name;

            if (!isset($tree[$resource])) {
                $tree[$resource] = [];
            }

            $tree[$resource][] = $action;
        }

        return $tree;
    }

    /**
     * Check if user can perform action on resource.
     */
    public function canOnResource(string $resource, string $action): bool
    {
        return $this->hasPermissionTo("{$resource}.{$action}");
    }

    /**
     * Get the default guard name.
     */
    protected function getDefaultGuardName(): string
    {
        return config('auth.defaults.guard', 'web');
    }

    /**
     * Convert permissions to permission models.
     */
    protected function convertToPermissionModels($permissions): array
    {
        if ($permissions instanceof Collection) {
            $permissions = $permissions->all();
        }

        return array_map(function ($permission) {
            if ($permission instanceof \Spatie\Permission\Models\Permission) {
                return $permission;
            }

            return $this->getPermissionClass()->findByName($permission, $this->getDefaultGuardName());
        }, $permissions);
    }
}