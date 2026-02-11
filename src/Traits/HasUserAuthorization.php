<?

namespace Kushelbek\FilamentUsersPermissionsRoles\Traits;

trait HasUserAuthorization
{
    /**
     * Check if current user has permission.
     */
    public function userCan(string $permission): bool
    {
        return auth()->check() && auth()->user()->can($permission);
    }

    /**
     * Check if current user has any of the permissions.
     */
    public function userCanAny(array $permissions): bool
    {
        if (!auth()->check()) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (auth()->user()->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if current user has all of the permissions.
     */
    public function userCanAll(array $permissions): bool
    {
        if (!auth()->check()) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (!auth()->user()->can($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if current user has role.
     */
    public function userHasRole(string $role): bool
    {
        return auth()->check() && auth()->user()->hasRole($role);
    }

    /**
     * Check if current user has any of the roles.
     */
    public function userHasAnyRole(array $roles): bool
    {
        if (!auth()->check()) {
            return false;
        }

        foreach ($roles as $role) {
            if (auth()->user()->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if current user is super admin.
     */
    public function userIsSuperAdmin(): bool
    {
        return $this->userHasRole(config('filament-users-permissions.super_admin_role', 'super-admin'));
    }

    /**
     * Check if current user is admin.
     */
    public function userIsAdmin(): bool
    {
        return $this->userHasRole(config('filament-users-permissions.admin_role', 'admin'));
    }

    /**
     * Authorize user action.
     */
    public function authorizeUser(string $permission, string $message = 'Unauthorized action.'): void
    {
        if (!$this->userCan($permission)) {
            abort(403, $message);
        }
    }

    /**
     * Authorize user action with policy.
     */
    public function authorizeUserAction($model, string $action, $arguments = []): void
    {
        if (!auth()->check() || !auth()->user()->can($action, $model)) {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Get current user permissions.
     */
    public function getUserPermissions(): array
    {
        return auth()->check() ? auth()->user()->getAllPermissionsNames() : [];
    }

    /**
     * Get current user roles.
     */
    public function getUserRoles(): array
    {
        return auth()->check() ? auth()->user()->getRoleNames() : [];
    }

    /**
     * Check if user can access Filament resource.
     */
    public function canAccessFilamentResource(string $resource): bool
    {
        $permissions = [
            $resource . '.viewAny',
            $resource . '.create',
            $resource . '.view',
            $resource . '.update',
            $resource . '.delete',
            $resource . '.restore',
            $resource . '.forceDelete',
        ];

        return $this->userCanAny($permissions);
    }

    /**
     * Filter items based on user permissions.
     */
    public function filterByUserPermission($query, string $resource, string $action = 'view'): void
    {
        if (!$this->userCan($resource . '.' . $action)) {
            $query->whereRaw('1 = 0'); // Возвращает пустой результат
        }
    }

    /**
     * Get accessible items for user.
     */
    public function getAccessibleItems($items, string $resource, string $action = 'view')
    {
        if ($this->userCan($resource . '.' . $action)) {
            return $items;
        }

        return collect();
    }
}