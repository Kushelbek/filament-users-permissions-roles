<?

namespace Kushelbek\FilamentUsersPermissionsRoles\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Database\Eloquent\Builder;

class Permission extends SpatiePermission
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'group',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($permission) {
            // Автоматически устанавливаем guard_name если не указан
            if (empty($permission->guard_name)) {
                $permission->guard_name = config('auth.defaults.guard', 'web');
            }
            
            // Автоматически определяем группу из имени разрешения
            if (empty($permission->group)) {
                $parts = explode('.', $permission->name);
                $permission->group = $parts[0] ?? 'other';
            }
        });

        static::updating(function ($permission) {
            // Обновляем группу при изменении имени
            if ($permission->isDirty('name')) {
                $parts = explode('.', $permission->name);
                $permission->group = $parts[0] ?? 'other';
            }
        });
    }

    /**
     * Scope a query to only include permissions of a specific group.
     */
    public function scopeGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    /**
     * Scope a query to only include permissions for specific guard.
     */
    public function scopeForGuard(Builder $query, string $guard): Builder
    {
        return $query->where('guard_name', $guard);
    }

    /**
     * Scope a query to only include permissions assigned to roles.
     */
    public function scopeAssigned(Builder $query): Builder
    {
        return $query->has('roles');
    }

    /**
     * Scope a query to only include unassigned permissions.
     */
    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->doesntHave('roles');
    }

    /**
     * Get the permission group.
     */
    public function getGroupAttribute(): string
    {
        if (!isset($this->attributes['group']) || empty($this->attributes['group'])) {
            $parts = explode('.', $this->name);
            return $parts[0] ?? 'other';
        }

        return $this->attributes['group'];
    }

    /**
     * Get the display name for the permission.
     */
    public function getDisplayNameAttribute(): string
    {
        $displayNames = config('filament-users-permissions.permission_display_names', []);
        
        if (isset($displayNames[$this->name])) {
            return $displayNames[$this->name];
        }

        // Преобразуем имя разрешения в читаемый формат
        $name = str_replace('.', ' ', $this->name);
        $name = str_replace(['-', '_'], ' ', $name);
        
        return ucwords($name);
    }

    /**
     * Get the action part of the permission name.
     */
    public function getActionAttribute(): string
    {
        $parts = explode('.', $this->name);
        return $parts[1] ?? $this->name;
    }

    /**
     * Get the resource part of the permission name.
     */
    public function getResourceAttribute(): string
    {
        $parts = explode('.', $this->name);
        return $parts[0] ?? 'other';
    }

    /**
     * Check if permission is a wildcard permission.
     */
    public function isWildcard(): bool
    {
        return strpos($this->name, '*') !== false || strpos($this->name, 'all') !== false;
    }

    /**
     * Check if permission is assigned to any role.
     */
    public function isAssigned(): bool
    {
        return $this->roles()->exists();
    }

    /**
     * Get all roles that have this permission.
     */
    public function getRoleNames(): array
    {
        return $this->roles()->pluck('name')->toArray();
    }

    /**
     * Assign permission to multiple roles.
     */
    public function assignToRoles(array $roleNames): self
    {
        $roles = \Spatie\Permission\Models\Role::whereIn('name', $roleNames)->get();
        
        foreach ($roles as $role) {
            if (!$role->hasPermissionTo($this)) {
                $role->givePermissionTo($this);
            }
        }

        // Очищаем кэш разрешений
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return $this;
    }

    /**
     * Remove permission from multiple roles.
     */
    public function removeFromRoles(array $roleNames): self
    {
        $roles = \Spatie\Permission\Models\Role::whereIn('name', $roleNames)->get();
        
        foreach ($roles as $role) {
            $role->revokePermissionTo($this);
        }

        // Очищаем кэш разрешений
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return $this;
    }
}