<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; // Importante

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
        {
            // Este comando intercepta todos los chequeos de permisos
            // Si el usuario tiene el rol 'Super Admin', se le concede el permiso sin buscar en la DB
            Gate::before(function ($user, $ability) {
                return $user->hasRole('Super Admin') ? true : null;
            });
        }
}
