<?

namespace Kushelbek\FilamentUsersPermissionsRoles\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use Filament\Facades\Filament;
use Kushelbek\FilamentUsersPermissionsRoles\Commands\InstallCommand;
use Kushelbek\FilamentUsersPermissionsRoles\Commands\SyncPermissionsCommand;

class FilamentUsersPermissionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Регистрируем Spatie Permission Service Provider если он еще не зарегистрирован
        if (!class_exists(\Spatie\Permission\PermissionServiceProvider::class)) {
            throw new \Exception('Spatie Laravel Permission package is required. Please install it first.');
        }
        
        $this->app->register(PermissionServiceProvider::class);
        
        $this->mergeConfigFrom(
            __DIR__.'/../../config/filament-users-permissions.php', 
            'filament-users-permissions'
        );
    }

    public function boot(): void
    {
        $this->checkSpatieInstallation();
        
        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
            $this->registerCommands();
        }
        
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'filament-users-permissions');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'filament-users-permissions');
        
        $this->registerFilamentResources();
    }
    
    /**
     * Проверяем установлен ли Spatie Laravel Permission
     */
    protected function checkSpatieInstallation(): void
    {
        if (!$this->app->providerIsLoaded(PermissionServiceProvider::class)) {
            if ($this->app->runningInConsole()) {
                $this->info('📦 Installing required dependency: spatie/laravel-permission');
                $this->call('composer require spatie/laravel-permission');
            } else {
                throw new \RuntimeException(
                    'Spatie Laravel Permission is not installed. ' .
                    'Please run: composer require spatie/laravel-permission'
                );
            }
        }
    }
    
    /**
     * Регистрируем команды публикации
     */
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
        
        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/filament-users-permissions'),
        ], 'filament-users-permissions-views');
        
        $this->publishes([
            __DIR__.'/../../stubs' => base_path('stubs/filament-users-permissions'),
        ], 'filament-users-permissions-stubs');
    }
    
    /**
     * Регистрируем Artisan команды
     */
    protected function registerCommands(): void
    {
        $this->commands([
            InstallCommand::class,
            SyncPermissionsCommand::class,
        ]);
    }
    
    /**
     * Регистрируем Filament ресурсы
     */
    protected function registerFilamentResources(): void
    {
        Filament::serving(function () {
            Filament::registerResources([
                \Kushelbek\FilamentUsersPermissionsRoles\Filament\Resources\RoleResource::class,
                \Kushelbek\FilamentUsersPermissionsRoles\Filament\Resources\PermissionResource::class,
            ]);
        });
    }
}