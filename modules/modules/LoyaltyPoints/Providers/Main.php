<?php

namespace Modules\LoyaltyPoints\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as Provider;

class Main extends Provider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadConfig();
        $this->loadViews();
        $this->loadViewComponents();
        $this->loadTranslations();
        $this->loadMigrations();
        $this->registerCommands();
        $this->registerSchedule();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->loadRoutes();
    }

    protected function loadConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('loyaltypoints.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'loyaltypoints'
        );
    }

    public function loadViews()
    {
        $viewPath = resource_path('views/modules/loyaltypoints');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path.'/modules/loyaltypoints';
        }, \Config::get('view.paths')), [$sourcePath]), 'loyaltypoints');
    }

    public function loadViewComponents()
    {
        Blade::componentNamespace('Modules\LoyaltyPoints\View\Components', 'loyaltypoints');
    }

    public function loadTranslations()
    {
        $langPath = resource_path('lang/modules/loyaltypoints');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'loyaltypoints');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang/en', 'loyaltypoints');
        }
    }

    public function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    public function loadRoutes()
    {
        if (app()->routesAreCached()) {
            return;
        }

        $routes = [
            'web.php',
            'api.php',
        ];

        foreach ($routes as $route) {
            $this->loadRoutesFrom(__DIR__.'/../Routes/'.$route);
        }
    }

    /**
     * Register the artisan command for this module.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\LoyaltyPoints\Console\Commands\AwardLoyaltyPoints::class,
            ]);
        }
    }

    /**
     * Self register the scheduled task that awards points after delivery,
     * without needing to touch app/Console/Kernel.php.
     *
     * @return void
     */
    protected function registerSchedule()
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('loyalty:award-points')
                ->everyFiveMinutes()
                ->withoutOverlapping()
                ->name('loyalty-award-points')
                ->onOneServer();
        });
    }

    public function provides()
    {
        return [];
    }
}
