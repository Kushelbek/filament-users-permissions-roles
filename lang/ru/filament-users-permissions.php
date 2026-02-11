<?

return [
    // Navigation
    'navigation.group' => 'Управление пользователями',
    'navigation.roles' => 'Роли',
    'navigation.permissions' => 'Разрешения',
    
    // Common
    'name' => 'Название',
    'description' => 'Описание',
    'guard_name' => 'Guard (защита)',
    'created_at' => 'Создано',
    'updated_at' => 'Обновлено',
    'actions' => 'Действия',
    'save' => 'Сохранить',
    'cancel' => 'Отмена',
    'delete' => 'Удалить',
    'edit' => 'Редактировать',
    'create' => 'Создать',
    'view' => 'Просмотр',
    'search' => 'Поиск',
    'filter' => 'Фильтр',
    'clear' => 'Очистить',
    'apply' => 'Применить',
    
    // Roles
    'role' => 'Роль',
    'roles' => 'Роли',
    'role_name' => 'Название роли',
    'role_description' => 'Описание роли',
    'create_role' => 'Создать роль',
    'edit_role' => 'Редактировать роль',
    'delete_role' => 'Удалить роль',
    'role_created' => 'Роль успешно создана',
    'role_updated' => 'Роль успешно обновлена',
    'role_deleted' => 'Роль успешно удалена',
    'protected_role' => 'Защищенная роль',
    'cannot_delete_protected_role' => 'Нельзя удалить защищенную роль: :role',
    'assign_permissions' => 'Назначить разрешения',
    'assigned_permissions' => 'Назначенные разрешения',
    'users_count' => 'Количество пользователей',
    'permissions_count' => 'Количество разрешений',
    
    // Permissions
    'permission' => 'Разрешение',
    'permissions' => 'Разрешения',
    'permission_name' => 'Название разрешения',
    'permission_description' => 'Описание разрешения',
    'create_permission' => 'Создать разрешение',
    'edit_permission' => 'Редактировать разрешение',
    'delete_permission' => 'Удалить разрешение',
    'permission_created' => 'Разрешение успешно создано',
    'permission_updated' => 'Разрешение успешно обновлено',
    'permission_deleted' => 'Разрешение успешно удалено',
    'permission_group' => 'Группа разрешений',
    'resource' => 'Ресурс',
    'action' => 'Действие',
    'wildcard_permission' => 'Wildcard разрешение',
    'assigned_to_roles' => 'Назначено ролям',
    'unassigned_permissions' => 'Неиспользуемые разрешения',
    
    // Users
    'user' => 'Пользователь',
    'users' => 'Пользователи',
    'user_roles' => 'Роли пользователя',
    'user_permissions' => 'Разрешения пользователя',
    'direct_permissions' => 'Прямые разрешения',
    'role_permissions' => 'Разрешения ролей',
    'all_permissions' => 'Все разрешения',
    'assign_roles' => 'Назначить роли',
    'assign_permissions_to_user' => 'Назначить разрешения пользователю',
    'is_super_admin' => 'Супер администратор',
    'access_level' => 'Уровень доступа',
    'last_permission_sync' => 'Последняя синхронизация разрешений',
    
    // Widget
    'stats' => 'Статистика',
    'total_roles' => 'Всего ролей',
    'total_permissions' => 'Всего разрешений',
    'users_with_roles' => 'Пользователей с ролями',
    'unused_permissions' => 'Неиспользуемые разрешения',
    'avg_permissions_per_role' => 'Ср. разрешений/роль',
    
    // Actions
    'sync_permissions' => 'Синхронизировать разрешения',
    'clear_cache' => 'Очистить кэш',
    'export' => 'Экспорт',
    'import' => 'Импорт',
    'bulk_actions' => 'Массовые действия',
    
    // Messages
    'confirm_delete' => 'Вы уверены, что хотите удалить это?',
    'confirm_delete_multiple' => 'Вы уверены, что хотите удалить выбранные элементы?',
    'no_items_selected' => 'Элементы не выбраны',
    'operation_successful' => 'Операция успешно выполнена',
    'operation_failed' => 'Операция не удалась',
    'cache_cleared' => 'Кэш успешно очищен',
    'permissions_synced' => 'Разрешения успешно синхронизированы',
    
    // Validation
    'validation.required' => 'Это поле обязательно',
    'validation.unique' => 'Это значение уже существует',
    'validation.max' => 'Максимум :max символов',
    'validation.regex' => 'Неверный формат. Используйте только буквы, цифры и точки',
    
    // Help
    'help.role_name' => 'Введите уникальное название для роли',
    'help.permission_name' => 'Формат: ресурс.действие (например, users.create)',
    'help.guard_name' => 'Guard, к которому относится эта роль/разрешение',
    'help.is_super_admin' => 'Пользователь с этой отметкой обходит все проверки разрешений',
    
    // Access Levels
    'access_levels' => [
        'basic' => 'Базовый',
        'extended' => 'Расширенный',
        'full' => 'Полный',
    ],
    
    // Groups
    'groups' => [
        'user' => 'Управление пользователями',
        'role' => 'Управление ролями',
        'permission' => 'Управление разрешениями',
        'post' => 'Управление статьями',
        'category' => 'Управление категориями',
        'system' => 'Система',
        'other' => 'Другое',
    ],
];