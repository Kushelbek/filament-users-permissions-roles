<?php

namespace Kushelbek\FilamentUsersPermissionsRoles\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
use Kushelbek\FilamentUsersPermissionsRoles\Commands\InstallCommand;
use Kushelbek\FilamentUsersPermissionsRoles\Commands\SyncPermissionsCommand;
use Kushelbek\FilamentUsersPermissionsRoles\Commands\PublishCommand;

class FilamentUsersPermissionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Регистрируем Spatie Permission ServiceProvider только если он установлен
        if (class_exists(\Spatie\Permission\PermissionServiceProvider::class)) {
            $this->app->register(\Spatie\Permission\PermissionServiceProvider::class);
        }
        
        $this->mergeConfigFrom(
            __DIR__.'/../../config/filament-users-permissions.php', 
            'filament-users-permissions'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
            $this->registerCommands();
        }
        
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'filament-users-permissions');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        
        $this->registerFilamentResources();
        $this->registerFilamentWidgets();
        
        // Регистрация событий временно отключена
        // $this->registerEvents();
    }
    
    protected function registerPublishing(): void
    {
        $this->publishes([
            __DIR__.'/../../config/filament-users-permissions.php' => config_path('filament-users-permissions.php'),
        ], 'filament-users-permissions-config');
        
        $this->publishes([
            __DIR__.'/../../database/migrations/' => database_path('migrations'),
        ], 'filament-users-permissions-migrations');
        
        $this->publishes([
            __DIR__.'/../../lang' => lang_path('vendor/filament-users-permissions'),
        ], 'filament-users-permissions-translations');
    }
    
    protected function registerCommands(): void
    {
        $this->commands([
            InstallCommand::class,
            SyncPermissionsCommand::class,
            PublishCommand::class,
        ]);
    }
    
    protected function registerFilamentResources(): void
    {
        if (class_exists(Filament::class)) {
            Filament::serving(function () {
                Filament::registerResources([
                    \Kushelbek\FilamentUsersPermissionsRoles\Filament\Resources\RoleResource::class,
                    \Kushelbek\FilamentUsersPermissionsRoles\Filament\Resources\PermissionResource::class,
                ]);
            });
        }
    }
    
    protected function registerFilamentWidgets(): void
    {
        if (class_exists(Filament::class)) {
            Filament::serving(function () {
                Filament::registerWidgets([
                    \Kushelbek\FilamentUsersPermissionsRoles\Filament\Widgets\PermissionStatsWidget::class,
                ]);
            });
        }
    }
    
    protected function registerEvents(): void
    {
        // Временно отключено
        // $this->app->register(EventServiceProvider::class);
    }
}