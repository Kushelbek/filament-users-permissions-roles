<?

namespace Kushelbek\FilamentUsersPermissionsRoles\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Kushelbek\FilamentUsersPermissionsRoles\FilamentUsersPermissions syncPermissions()
 * @method static \Kushelbek\FilamentUsersPermissionsRoles\FilamentUsersPermissions getPermissionGroups()
 * @method static \Kushelbek\FilamentUsersPermissionsRoles\FilamentUsersPermissions getRoleStats()
 * @method static \Kushelbek\FilamentUsersPermissionsRoles\FilamentUsersPermissions clearCache()
 * @method static \Kushelbek\FilamentUsersPermissionsRoles\FilamentUsersPermissions getUserPermissions(\Illuminate\Foundation\Auth\User $user)
 * @method static \Kushelbek\FilamentUsersPermissionsRoles\FilamentUsersPermissions checkAccess(\Illuminate\Foundation\Auth\User $user, string $permission)
 * 
 * @see \Kushelbek\FilamentUsersPermissionsRoles\FilamentUsersPermissions
 */
class FilamentUsersPermissions extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'filament-users-permissions';
    }
}