<?php

namespace Najaz\Admin\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerConfig();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../Resources/views' => resource_path('themes/admin/views'),
        ]);
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        // Load auth routes to override Webkul admin login routes
        // Using booted callback to ensure routes load after Webkul routes
        $this->app->booted(function () {
            Route::middleware(['web'])->group(__DIR__.'/../Routes/auth-routes.php');
        });

        $this->loadRoutesFrom(__DIR__.'/../Routes/admin-routes.php');

        $this->loadRoutesFrom(__DIR__.'/../Routes/dashboard-routes.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/citizen-routes.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/service-routes.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/service-request-routes.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/notification-routes.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/reporting-routes.php');
        
        // Load settings routes to override Webkul routes
        $this->app->booted(function () {
            Route::middleware(['web', 'admin'])->prefix(config('app.admin_url'))->group(__DIR__.'/../Routes/settings-routes.php');
            Route::middleware(['web', 'admin'])->prefix(config('app.admin_url'))->group(__DIR__.'/../Routes/dashboard-routes.php');

        });

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'Admin');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'admin');

        Event::listen('bagisto.admin.layout.head', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('admin::admin.layouts.style');
        });
    }

    /**
     * Register package config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/menu.php', 'menu.admin'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/acl.php', 'acl'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/system.php', 'core'
        );
    }
}
