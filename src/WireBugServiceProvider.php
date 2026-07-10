<?php

namespace EduLazaro\WireBug;

use EduLazaro\WireBug\Http\Controllers\WireBugController;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class WireBugServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/wirebug.php', 'wirebug');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'wirebug');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'wirebug');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Registramos el componente como `<x-wirebug />` (sin namespace
        // explícito en la blade del consumidor).
        Blade::component('wirebug::components.wirebug', 'wirebug');

        if (config('wirebug.route.enabled', true)) {
            Route::post(config('wirebug.route.path', 'wirebug'), WireBugController::class)
                ->middleware(config('wirebug.route.middleware', ['web', 'throttle:10,1']))
                ->name('wirebug.store');
        }

        $this->publishes([
            __DIR__ . '/../config/wirebug.php' => config_path('wirebug.php'),
        ], 'wirebug-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/wirebug'),
        ], 'wirebug-views');

        $this->publishes([
            __DIR__ . '/../resources/css/wirebug.css' => resource_path('css/vendor/wirebug.css'),
        ], 'wirebug-css');

        $this->publishes([
            __DIR__ . '/../lang' => $this->app->langPath('vendor/wirebug'),
        ], 'wirebug-lang');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'wirebug-migrations');
    }
}
