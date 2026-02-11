<?

return [
    // Navigation
    'navigation.group' => 'User Management',
    'navigation.roles' => 'Roles',
    'navigation.permissions' => 'Permissions',
    
    // Common
    'name' => 'Name',
    'description' => 'Description',
    'guard_name' => 'Guard Name',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',
    'actions' => 'Actions',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'delete' => 'Delete',
    'edit' => 'Edit',
    'create' => 'Create',
    'view' => 'View',
    'search' => 'Search',
    'filter' => 'Filter',
    'clear' => 'Clear',
    'apply' => 'Apply',
    
    // Roles
    'role' => 'Role',
    'roles' => 'Roles',
    'role_name' => 'Role Name',
    'role_description' => 'Role Description',
    'create_role' => 'Create Role',
    'edit_role' => 'Edit Role',
    'delete_role' => 'Delete Role',
    'role_created' => 'Role created successfully',
    'role_updated' => 'Role updated successfully',
    'role_deleted' => 'Role deleted successfully',
    'protected_role' => 'Protected Role',
    'cannot_delete_protected_role' => 'Cannot delete protected role: :role',
    'assign_permissions' => 'Assign Permissions',
    'assigned_permissions' => 'Assigned Permissions',
    'users_count' => 'Users Count',
    'permissions_count' => 'Permissions Count',
    
    // Permissions
    'permission' => 'Permission',
    'permissions' => 'Permissions',
    'permission_name' => 'Permission Name',
    'permission_description' => 'Permission Description',
    'create_permission' => 'Create Permission',
    'edit_permission' => 'Edit Permission',
    'delete_permission' => 'Delete Permission',
    'permission_created' => 'Permission created successfully',
    'permission_updated' => 'Permission updated successfully',
    'permission_deleted' => 'Permission deleted successfully',
    'permission_group' => 'Permission Group',
    'resource' => 'Resource',
    'action' => 'Action',
    'wildcard_permission' => 'Wildcard Permission',
    'assigned_to_roles' => 'Assigned to Roles',
    'unassigned_permissions' => 'Unassigned Permissions',
    
    // Users
    'user' => 'User',
    'users' => 'Users',
    'user_roles' => 'User Roles',
    'user_permissions' => 'User Permissions',
    'direct_permissions' => 'Direct Permissions',
    'role_permissions' => 'Role Permissions',
    'all_permissions' => 'All Permissions',
    'assign_roles' => 'Assign Roles',
    'assign_permissions_to_user' => 'Assign Permissions to User',
    'is_super_admin' => 'Super Administrator',
    'access_level' => 'Access Level',
    'last_permission_sync' => 'Last Permission Sync',
    
    // Widget
    'stats' => 'Statistics',
    'total_roles' => 'Total Roles',
    'total_permissions' => 'Total Permissions',
    'users_with_roles' => 'Users with Roles',
    'unused_permissions' => 'Unused Permissions',
    'avg_permissions_per_role' => 'Avg. Permissions/Role',
    
    // Actions
    'sync_permissions' => 'Sync Permissions',
    'clear_cache' => 'Clear Cache',
    'export' => 'Export',
    'import' => 'Import',
    'bulk_actions' => 'Bulk Actions',
    
    // Messages
    'confirm_delete' => 'Are you sure you want to delete this?',
    'confirm_delete_multiple' => 'Are you sure you want to delete selected items?',
    'no_items_selected' => 'No items selected',
    'operation_successful' => 'Operation completed successfully',
    'operation_failed' => 'Operation failed',
    'cache_cleared' => 'Cache cleared successfully',
    'permissions_synced' => 'Permissions synced successfully',
    
    // Validation
    'validation.required' => 'This field is required',
    'validation.unique' => 'This value already exists',
    'validation.max' => 'Maximum :max characters allowed',
    'validation.regex' => 'Invalid format. Use only letters, numbers and dots',
    
    // Help
    'help.role_name' => 'Enter a unique name for the role',
    'help.permission_name' => 'Format: resource.action (e.g., users.create)',
    'help.guard_name' => 'The guard that this role/permission belongs to',
    'help.is_super_admin' => 'User with this flag bypasses all permission checks',
    
    // Access Levels
    'access_levels' => [
        'basic' => 'Basic',
        'extended' => 'Extended',
        'full' => 'Full',
    ],
    
    // Groups
    'groups' => [
        'user' => 'User Management',
        'role' => 'Role Management',
        'permission' => 'Permission Management',
        'post' => 'Post Management',
        'category' => 'Category Management',
        'system' => 'System',
        'other' => 'Other',
    ],
];