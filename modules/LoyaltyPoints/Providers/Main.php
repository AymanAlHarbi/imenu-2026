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
        $this->autoInstallTables();
        $this->opportunisticAwardPoints();
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

    /**
     * Self-healing installer: creates this module's own database tables on
     * the very first request after upload, with no need for SSH/Terminal
     * access. Some hosting/admin panels only copy the plugin files without
     * ever running `php artisan module:migrate`, so we can't rely on that
     * step happening. This checks a lightweight flag file first, so after
     * the first successful run it costs nothing on further requests.
     *
     * @return void
     */
    protected function autoInstallTables()
    {
        $flagFile = storage_path('app/loyaltypoints_installed.flag');

        if (file_exists($flagFile)) {
            return;
        }

        try {
            $needsInstall = ! \Illuminate\Support\Facades\Schema::hasTable('loyalty_accounts')
                || ! \Illuminate\Support\Facades\Schema::hasTable('loyalty_transactions');

            if ($needsInstall) {
                $migrationsPath = __DIR__.'/../Database/Migrations';

                // Path passed to `migrate --path` must be relative to base_path()
                $relativePath = ltrim(str_replace(base_path(), '', realpath($migrationsPath)), DIRECTORY_SEPARATOR);

                \Illuminate\Support\Facades\Artisan::call('migrate', [
                    '--path' => $relativePath,
                    '--force' => true,
                ]);
            }

            // Only write the flag once we're sure the tables actually exist,
            // otherwise keep retrying on the next request.
            if (\Illuminate\Support\Facades\Schema::hasTable('loyalty_accounts')
                && \Illuminate\Support\Facades\Schema::hasTable('loyalty_transactions')) {
                @file_put_contents($flagFile, (string) now());
            }
        } catch (\Throwable $e) {
            // Never let an install hiccup break the rest of the site.
            // It will simply be retried on the next request.
        }
    }

    /**
     * Many shared-hosting / no-terminal setups never get a cron job wired
     * up to run `php artisan schedule:run`, which means the scheduled
     * command registered in registerSchedule() would silently never fire.
     * As a safety net, opportunistically run the award-points command on
     * normal website visits too (throttled to once every 5 minutes, and
     * only after the response has been sent to the visitor so it never
     * slows down page loads).
     *
     * @return void
     */
    protected function opportunisticAwardPoints()
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $flagFile = storage_path('app/loyaltypoints_last_run.flag');
        $lastRun = file_exists($flagFile) ? (int) @file_get_contents($flagFile) : 0;

        if ((time() - $lastRun) < 300) {
            return;
        }

        // Claim this run immediately so concurrent requests don't all trigger it
        @file_put_contents($flagFile, (string) time());

        $this->app->terminating(function () {
            try {
                \Illuminate\Support\Facades\Artisan::call('loyalty:award-points');
            } catch (\Throwable $e) {
                // Ignore - will simply retry on a later visit
            }
        });
    }

    public function provides()
    {
        return [];
    }
}
