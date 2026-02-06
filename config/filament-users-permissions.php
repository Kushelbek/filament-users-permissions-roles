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
            'permissions' => ['*'],
        ],
        [
            'name' => 'admin',
            'permissions' => [
                'user.view',
                'user.create',
                'user.edit',
                'user.delete',
                'role.view',
                'role.create',
                'role.edit',
                'role.delete',
                'permission.view',
                'permission.create',
                'permission.edit',
                'permission.delete',
            ],
        ],
        [
            'name' => 'editor',
            'permissions' => [
                'user.view',
                'post.view',
                'post.create',
                'post.edit',
            ],
        ],
        [
            'name' => 'viewer',
            'permissions' => [
                'user.view',
                'post.view',
            ],
        ],
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
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'duration' => 3600, // seconds
        'key' => 'spatie.permission.cache',
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
    ],
];