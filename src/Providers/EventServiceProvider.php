<?

namespace Kushelbek\FilamentUsersPermissionsRoles\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Kushelbek\FilamentUsersPermissionsRoles\Events\RoleCreated;
use Kushelbek\FilamentUsersPermissionsRoles\Events\RoleUpdated;
use Kushelbek\FilamentUsersPermissionsRoles\Events\RoleDeleted;
use Kushelbek\FilamentUsersPermissionsRoles\Events\PermissionCreated;
use Kushelbek\FilamentUsersPermissionsRoles\Events\PermissionUpdated;
use Kushelbek\FilamentUsersPermissionsRoles\Events\PermissionDeleted;
use Kushelbek\FilamentUsersPermissionsRoles\Events\UserRolesUpdated;
use Kushelbek\FilamentUsersPermissionsRoles\Events\UserPermissionsUpdated;
use Kushelbek\FilamentUsersPermissionsRoles\Listeners\ClearPermissionCache;
use Kushelbek\FilamentUsersPermissionsRoles\Listeners\LogRoleActivity;
use Kushelbek\FilamentUsersPermissionsRoles\Listeners\LogPermissionActivity;
use Kushelbek\FilamentUsersPermissionsRoles\Listeners\SendRoleNotification;
use Kushelbek\FilamentUsersPermissionsRoles\Listeners\SendPermissionNotification;
use Kushelbek\FilamentUsersPermissionsRoles\Listeners\UpdateUserAccessLog;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the package.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        RoleCreated::class => [
            ClearPermissionCache::class,
            LogRoleActivity::class,
            SendRoleNotification::class,
        ],
        
        RoleUpdated::class => [
            ClearPermissionCache::class,
            LogRoleActivity::class,
        ],
        
        RoleDeleted::class => [
            ClearPermissionCache::class,
            LogRoleActivity::class,
            SendRoleNotification::class,
        ],
        
        PermissionCreated::class => [
            ClearPermissionCache::class,
            LogPermissionActivity::class,
            SendPermissionNotification::class,
        ],
        
        PermissionUpdated::class => [
            ClearPermissionCache::class,
            LogPermissionActivity::class,
        ],
        
        PermissionDeleted::class => [
            ClearPermissionCache::class,
            LogPermissionActivity::class,
            SendPermissionNotification::class,
        ],
        
        UserRolesUpdated::class => [
            ClearPermissionCache::class,
            UpdateUserAccessLog::class,
        ],
        
        UserPermissionsUpdated::class => [
            ClearPermissionCache::class,
            UpdateUserAccessLog::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        // Дополнительная регистрация событий
        $this->registerModelEvents();
    }

    /**
     * Register model events.
     */
    protected function registerModelEvents(): void
    {
        // События для моделей Spatie
        \Spatie\Permission\Models\Role::created(function ($role) {
            event(new RoleCreated($role));
        });

        \Spatie\Permission\Models\Role::updated(function ($role) {
            event(new RoleUpdated($role));
        });

        \Spatie\Permission\Models\Role::deleted(function ($role) {
            event(new RoleDeleted($role));
        });

        \Spatie\Permission\Models\Permission::created(function ($permission) {
            event(new PermissionCreated($permission));
        });

        \Spatie\Permission\Models\Permission::updated(function ($permission) {
            event(new PermissionUpdated($permission));
        });

        \Spatie\Permission\Models\Permission::deleted(function ($permission) {
            event(new PermissionDeleted($permission));
        });

        // События для пользователей
        $userModel = config('auth.providers.users.model', 'App\\Models\\User');
        
        if (class_exists($userModel)) {
            $userModel::saved(function ($user) {
                if ($user->isDirty()) {
                    // Проверяем, изменились ли роли или разрешения
                    $originalRoles = $user->getOriginal('roles') ?? [];
                    $currentRoles = $user->roles->pluck('id')->toArray();
                    
                    $originalPermissions = $user->getOriginal('permissions') ?? [];
                    $currentPermissions = $user->permissions->pluck('id')->toArray();
                    
                    if ($originalRoles != $currentRoles) {
                        event(new UserRolesUpdated($user, $originalRoles, $currentRoles));
                    }
                    
                    if ($originalPermissions != $currentPermissions) {
                        event(new UserPermissionsUpdated($user, $originalPermissions, $currentPermissions));
                    }
                }
            });
        }
    }
}