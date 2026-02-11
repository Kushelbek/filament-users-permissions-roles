<?

return [
    /*
    |--------------------------------------------------------------------------
    | Protected Roles
    |--------------------------------------------------------------------------
    |
    | Roles that cannot be deleted or modified through the interface.
    | These are typically system roles like 'super-admin', 'admin'.
    |
    */
    'protected_roles' => [
        'super-admin',
        'admin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Roles
    |--------------------------------------------------------------------------
    |
    | Default roles to be created during installation.
    | Format: ['name' => 'role_name', 'permissions' => ['permission1', 'permission2']]
    |
    */
    'default_roles' => [
        [
            'name' => 'super-admin',
            'guard_name' => 'web',
            'description' => 'Super Administrator with all permissions',
            'permissions' => ['*'],
        ],
        [
            'name' => 'admin',
            'guard_name' => 'web',
            'description' => 'Administrator with management permissions',
            'permissions' => [
                'users.viewAny',
                'users.view',
                'users.create',
                'users.update',
                'users.delete',
                'roles.viewAny',
                'roles.view',
                'roles.create',
                'roles.update',
                'roles.delete',
                'permissions.viewAny',
                'permissions.view',
                'permissions.create',
                'permissions.update',
                'permissions.delete',
            ],
        ],
        [
            'name' => 'editor',
            'guard_name' => 'web',
            'description' => 'Editor with content management permissions',
            'permissions' => [
                'posts.viewAny',
                'posts.view',
                'posts.create',
                'posts.update',
                'categories.viewAny',
                'categories.view',
                'categories.create',
                'categories.update',
            ],
        ],
        [
            'name' => 'viewer',
            'guard_name' => 'web',
            'description' => 'Viewer with read-only permissions',
            'permissions' => [
                'posts.viewAny',
                'posts.view',
                'categories.viewAny',
                'categories.view',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Permissions
    |--------------------------------------------------------------------------
    |
    | Default permissions to be created during installation.
    |
    */
    'default_permissions' => [
        // User Management
        'users.viewAny',
        'users.view',
        'users.create',
        'users.update',
        'users.delete',
        'users.restore',
        'users.forceDelete',

        // Role Management
        'roles.viewAny',
        'roles.view',
        'roles.create',
        'roles.update',
        'roles.delete',

        // Permission Management
        'permissions.viewAny',
        'permissions.view',
        'permissions.create',
        'permissions.update',
        'permissions.delete',

        // Content Management
        'posts.viewAny',
        'posts.view',
        'posts.create',
        'posts.update',
        'posts.delete',
        'posts.restore',
        'posts.forceDelete',

        'categories.viewAny',
        'categories.view',
        'categories.create',
        'categories.update',
        'categories.delete',

        // System
        'system.settings.view',
        'system.settings.update',
        'system.backup.create',
        'system.backup.download',
        'system.backup.delete',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Groups
    |--------------------------------------------------------------------------
    |
    | Group permissions for better organization in the UI.
    | The key is the group name, value is array of permission patterns.
    |
    */
    'permission_groups' => [
        'user' => ['user.*'],
        'role' => ['role.*'],
        'permission' => ['permission.*'],
        'post' => ['post.*'],
        'category' => ['category.*'],
        'system' => ['system.*'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Display Names
    |--------------------------------------------------------------------------
    |
    | Custom display names for roles in the UI.
    |
    */
    'role_display_names' => [
        'super-admin' => 'Super Administrator',
        'admin' => 'Administrator',
        'editor' => 'Editor',
        'viewer' => 'Viewer',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Display Names
    |--------------------------------------------------------------------------
    |
    | Custom display names for permissions in the UI.
    |
    */
    'permission_display_names' => [
        'users.viewAny' => 'View Users List',
        'users.view' => 'View User Details',
        'users.create' => 'Create Users',
        'users.update' => 'Update Users',
        'users.delete' => 'Delete Users',
        'users.restore' => 'Restore Users',
        'users.forceDelete' => 'Permanently Delete Users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Colors
    |--------------------------------------------------------------------------
    |
    | Colors for role badges in the UI.
    | Available colors: primary, secondary, success, danger, warning, info, gray
    |
    */
    'role_colors' => [
        'super-admin' => 'danger',
        'admin' => 'primary',
        'editor' => 'success',
        'viewer' => 'info',
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Settings
    |--------------------------------------------------------------------------
    */
    'ui' => [
        'navigation' => [
            'group' => 'User Management',
            'sort' => 10,
            'icon' => 'heroicon-o-users',
        ],
        'show_direct_permissions' => true,
        'show_guard_selector' => true,
        'show_permission_groups' => true,
        'bulk_actions' => true,
        'permission_tree' => true,
        'role_badges' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'duration' => 3600, // seconds
        'permission_key' => 'spatie.permission.cache',
        'role_key' => 'filament_users_permissions.roles',
        'user_key_prefix' => 'filament_users_permissions.user',
    ],

    /*
    |--------------------------------------------------------------------------
    | Advanced Features
    |--------------------------------------------------------------------------
    */
    'features' => [
        'teams' => false,
        'wildcard_permissions' => true,
        'permission_inheritance' => true,
        'audit_log' => true,
        'permission_sync' => true,
        'role_hierarchy' => false,
        'time_based_permissions' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Log Settings
    |--------------------------------------------------------------------------
    */
    'audit_log' => [
        'enabled' => true,
        'retention_days' => 90,
        'log_events' => [
            'role.created',
            'role.updated',
            'role.deleted',
            'permission.created',
            'permission.updated',
            'permission.deleted',
            'user.role.assigned',
            'user.role.revoked',
            'user.permission.assigned',
            'user.permission.revoked',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Super Admin Settings
    |--------------------------------------------------------------------------
    */
    'super_admin' => [
        'role_name' => 'super-admin',
        'bypass_all_permissions' => true,
        'can_manage_self' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Guard Configuration
    |--------------------------------------------------------------------------
    */
    'guards' => [
        'default' => 'web',
        'available' => ['web', 'api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'role_name' => 'required|string|max:255|unique:roles,name',
        'permission_name' => 'required|string|max:255|regex:/^[a-z0-9\.]+$/i',
    ],
];