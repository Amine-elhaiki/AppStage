<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;

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
        // Configuration de la base de données
        Schema::defaultStringLength(191);

        // Configuration de la pagination pour utiliser Bootstrap
        Paginator::useBootstrapFive();

        // Configuration de Carbon en français
        Carbon::setLocale('fr');

        // Partage de données globales avec toutes les vues
        view()->composer('*', function ($view) {
            $view->with([
                'currentUser' => auth()->user(),
                'appName' => config('app.name', 'PlanifTech ORMVAT'),
                'appVersion' => config('app.version', '1.0.0'),
            ]);
        });

        // Configuration de Carbon pour l'affichage des dates en français
        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fra');
    }
}
