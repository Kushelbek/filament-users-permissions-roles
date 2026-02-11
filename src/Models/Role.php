<?

namespace Kushelbek\FilamentUsersPermissionsRoles\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Role extends SpatieRole
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
        'is_protected',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_protected' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($role) {
            // Автоматически устанавливаем guard_name если не указан
            if (empty($role->guard_name)) {
                $role->guard_name = config('auth.defaults.guard', 'web');
            }
        });

        static::deleting(function ($role) {
            // Запрещаем удаление защищенных ролей
            if (in_array($role->name, config('filament-users-permissions.protected_roles', []))) {
                throw new \Exception("Cannot delete protected role: {$role->name}");
            }
        });
    }

    /**
     * Scope a query to only include protected roles.
     */
    public function scopeProtected(Builder $query): Builder
    {
        return $query->whereIn('name', config('filament-users-permissions.protected_roles', []));
    }

    /**
     * Scope a query to only include non-protected roles.
     */
    public function scopeNotProtected(Builder $query): Builder
    {
        return $query->whereNotIn('name', config('filament-users-permissions.protected_roles', []));
    }

    /**
     * Check if role is protected.
     */
    public function isProtected(): bool
    {
        return in_array($this->name, config('filament-users-permissions.protected_roles', []));
    }

    /**
     * Get all permissions grouped by type.
     */
    public function getGroupedPermissions(): Collection
    {
        return $this->permissions->groupBy(function ($permission) {
            $parts = explode('.', $permission->name);
            return $parts[0] ?? 'other';
        });
    }

    /**
     * Sync permissions with validation.
     */
    public function syncPermissionsWithValidation(array $permissions): self
    {
        $validPermissions = [];

        foreach ($permissions as $permission) {
            if (is_string($permission)) {
                $validPermissions[] = $permission;
            } elseif ($permission instanceof \Spatie\Permission\Models\Permission) {
                $validPermissions[] = $permission->name;
            }
        }

        $this->syncPermissions($validPermissions);

        // Очищаем кэш разрешений
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return $this;
    }

    /**
     * Check if role has all given permissions.
     */
    public function hasAllPermissions(array $permissionNames): bool
    {
        $rolePermissions = $this->permissions->pluck('name')->toArray();
        
        foreach ($permissionNames as $permission) {
            if (!in_array($permission, $rolePermissions)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Check if role has any of the given permissions.
     */
    public function hasAnyPermission(array $permissionNames): bool
    {
        $rolePermissions = $this->permissions->pluck('name')->toArray();
        
        foreach ($permissionNames as $permission) {
            if (in_array($permission, $rolePermissions)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get the display name for the role.
     */
    public function getDisplayNameAttribute(): string
    {
        $displayNames = config('filament-users-permissions.role_display_names', []);
        
        return $displayNames[$this->name] ?? ucwords(str_replace(['-', '_'], ' ', $this->name));
    }

    /**
     * Get the color for the role badge.
     */
    public function getBadgeColorAttribute(): string
    {
        $colors = config('filament-users-permissions.role_colors', [
            'super-admin' => 'danger',
            'admin' => 'primary',
            'editor' => 'success',
            'viewer' => 'info',
        ]);

        return $colors[$this->name] ?? 'gray';
    }
}