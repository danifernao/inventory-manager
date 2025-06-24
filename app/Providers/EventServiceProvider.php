<?php

namespace App\Providers;

use App\Models\Movimiento;
use App\Models\Bodega;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\Proyecto;
use App\Models\Unidad;
use App\Observers\BodegaObserver;
use App\Observers\EmpresaObserver;
use App\Observers\ProductoObserver;
use App\Observers\ProyectoObserver;
use App\Observers\UnidadObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Bodega::observe(BodegaObserver::class);
        Empresa::observe(EmpresaObserver::class);
        Producto::observe(ProductoObserver::class);
        Proyecto::observe(ProyectoObserver::class);
        Unidad::observe(UnidadObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
