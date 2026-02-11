<?php

namespace Kushelbek\FilamentUsersPermissionsRoles\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the package.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        // События временно отключены для стабильности
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        // Модельные события отключены
    }
}