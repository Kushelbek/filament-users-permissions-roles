<?

namespace Kushelbek\FilamentUsersPermissionsRoles\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Очищаем кэш разрешений
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Получаем конфигурацию по умолчанию
        $config = config('filament-users-permissions', [
            'default_roles' => [],
            'default_permissions' => [],
        ]);

        // Создаем разрешения
        $this->createPermissions($config['default_permissions'] ?? []);

        // Создаем роли и назначаем разрешения
        $this->createRolesWithPermissions($config['default_roles'] ?? []);

        // Создаем системные разрешения если их нет
        $this->createSystemPermissions();

        $this->command->info('✅ Permissions and roles seeded successfully!');
    }

    protected function createPermissions(array $permissions): void
    {
        $created = 0;
        $skipped = 0;

        foreach ($permissions as $permissionData) {
            if (is_string($permissionData)) {
                $permissionData = ['name' => $permissionData];
            }

            $permission = Permission::firstOrCreate([
                'name' => $permissionData['name'],
                'guard_name' => $permissionData['guard_name'] ?? 'web',
            ], [
                'description' => $permissionData['description'] ?? null,
            ]);

            if ($permission->wasRecentlyCreated) {
                $created++;
                $this->command->info("  Created permission: {$permission->name}");
            } else {
                $skipped++;
            }
        }

        $this->command->info("  Permissions: {$created} created, {$skipped} already exist");
    }

    protected function createRolesWithPermissions(array $roles): void
    {
        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate([
                'name' => $roleData['name'],
                'guard_name' => $roleData['guard_name'] ?? 'web',
            ], [
                'description' => $roleData['description'] ?? null,
            ]);

            if ($role->wasRecentlyCreated) {
                $this->command->info("  Created role: {$role->name}");
            }

            // Назначаем разрешения роли
            if (isset($roleData['permissions']) && is_array($roleData['permissions'])) {
                $permissions = [];
                
                foreach ($roleData['permissions'] as $permissionName) {
                    if ($permissionName === '*') {
                        // Даем все разрешения
                        $permissions = Permission::pluck('name')->toArray();
                        break;
                    }
                    
                    $permission = Permission::where('name', $permissionName)
                        ->where('guard_name', $roleData['guard_name'] ?? 'web')
                        ->first();
                    
                    if ($permission) {
                        $permissions[] = $permissionName;
                    } else {
                        $this->command->warn("  Permission not found: {$permissionName}");
                    }
                }
                
                $role->syncPermissions($permissions);
                $this->command->info("  Assigned " . count($permissions) . " permissions to role: {$role->name}");
            }
        }
    }

    protected function createSystemPermissions(): void
    {
        // Системные разрешения, которые всегда должны быть
        $systemPermissions = [
            // Управление пользователями
            'users.viewAny',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.restore',
            'users.forceDelete',
            
            // Управление ролями
            'roles.viewAny',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            
            // Управление разрешениями
            'permissions.viewAny',
            'permissions.view',
            'permissions.create',
            'permissions.update',
            'permissions.delete',
            
            // Системные
            'system.settings.view',
            'system.settings.update',
            'system.backup.create',
            'system.backup.download',
            'system.backup.delete',
        ];

        foreach ($systemPermissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // Проверяем наличие супер-администратора
        $superAdminRole = Role::where('name', 'super-admin')->first();
        
        if (!$superAdminRole) {
            $superAdminRole = Role::create([
                'name' => 'super-admin',
                'guard_name' => 'web',
                'description' => 'Super Administrator with all permissions',
            ]);
            
            // Даем все разрешения супер-администратору
            $allPermissions = Permission::pluck('name')->toArray();
            $superAdminRole->syncPermissions($allPermissions);
            
            $this->command->info('  Created super-admin role with all permissions');
        }

        // Создаем администратора если его нет
        $adminRole = Role::where('name', 'admin')->first();
        
        if (!$adminRole) {
            $adminRole = Role::create([
                'name' => 'admin',
                'guard_name' => 'web',
                'description' => 'Administrator with management permissions',
            ]);
            
            // Даем базовые разрешения администратору
            $adminPermissions = [
                'users.viewAny',
                'users.view',
                'users.create',
                'users.update',
                'roles.viewAny',
                'roles.view',
                'permissions.viewAny',
                'permissions.view',
            ];
            
            $adminRole->syncPermissions($adminPermissions);
            
            $this->command->info('  Created admin role with basic permissions');
        }
    }

    public static function seedSuperAdminUser($user): void
    {
        $superAdminRole = Role::where('name', 'super-admin')->first();
        
        if ($superAdminRole && $user) {
            $user->assignRole($superAdminRole);
            $user->is_super_admin = true;
            $user->save();
        }
    }
}