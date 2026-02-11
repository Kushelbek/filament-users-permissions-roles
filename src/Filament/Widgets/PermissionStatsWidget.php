<?

namespace Kushelbek\FilamentUsersPermissionsRoles\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    protected static ?int $sort = 1;
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $cacheKey = 'permission_stats_' . md5(serialize([
            Role::count(),
            Permission::count(),
        ]));
        
        $stats = cache()->remember($cacheKey, 300, function () {
            return [
                'roles' => Role::count(),
                'permissions' => Permission::count(),
                'users_with_roles' => $this->getUsersWithRolesCount(),
                'unused_permissions' => $this->getUnusedPermissionsCount(),
                'avg_permissions_per_role' => $this->getAvgPermissionsPerRole(),
            ];
        });

        return [
            Stat::make('Total Roles', $stats['roles'])
                ->description('Active roles in system')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary')
                ->chart($this->getRoleGrowthChart())
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'wire:click' => "redirectToRoute('filament.admin.resources.roles.index')",
                ]),

            Stat::make('Total Permissions', $stats['permissions'])
                ->description('Available permissions')
                ->descriptionIcon('heroicon-o-key')
                ->color('success')
                ->chart($this->getPermissionGrowthChart()),

            Stat::make('Users with Roles', $stats['users_with_roles'])
                ->description('Users assigned to roles')
                ->descriptionIcon('heroicon-o-users')
                ->color('info')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'wire:click' => "redirectToRoute('filament.admin.resources.users.index')",
                ]),

            Stat::make('Unused Permissions', $stats['unused_permissions'])
                ->description('Permissions not assigned to any role')
                ->descriptionIcon('heroicon-o-exclamation-circle')
                ->color($stats['unused_permissions'] > 0 ? 'warning' : 'gray')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'wire:click' => "redirectToRoute('filament.admin.resources.permissions.index', ['tableFilters[has_roles][value]' => '0'])",
                ]),

            Stat::make('Avg. Permissions/Role', round($stats['avg_permissions_per_role'], 1))
                ->description('Average permissions per role')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('danger'),
        ];
    }
    
    protected function getUsersWithRolesCount(): int
    {
        return DB::table('model_has_roles')
            ->where('model_type', config('auth.providers.users.model', 'App\\Models\\User'))
            ->distinct('model_id')
            ->count('model_id');
    }
    
    protected function getUnusedPermissionsCount(): int
    {
        return Permission::doesntHave('roles')->count();
    }
    
    protected function getAvgPermissionsPerRole(): float
    {
        $totalPermissions = DB::table('role_has_permissions')->count();
        $totalRoles = Role::has('permissions')->count();
        
        return $totalRoles > 0 ? $totalPermissions / $totalRoles : 0;
    }
    
    protected function getRoleGrowthChart(): array
    {
        // Простая статистика за последние 7 дней
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $count = Role::whereDate('created_at', '<=', $date)->count();
            $data[] = $count;
        }
        
        return $data;
    }
    
    protected function getPermissionGrowthChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $count = Permission::whereDate('created_at', '<=', $date)->count();
            $data[] = $count;
        }
        
        return $data;
    }
    
    public static function canView(): bool
    {
        return auth()->user()->can('viewAny', Role::class) || 
               auth()->user()->can('viewAny', Permission::class);
    }
}