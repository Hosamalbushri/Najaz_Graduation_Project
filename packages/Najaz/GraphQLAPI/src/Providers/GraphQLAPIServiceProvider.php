<?php

namespace Najaz\GraphQLAPI\Providers;

use Illuminate\Support\ServiceProvider;
use Najaz\Citizen\Contracts\Citizen as CitizenContract;
use Najaz\GraphQLAPI\Models\Citizen\Citizen;
use Najaz\GraphQLAPI\NajazGraphql;

class GraphQLAPIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(NajazGraphql::class, fn () => new NajazGraphql());

        $this->app->alias(NajazGraphql::class, 'najaz_graphql');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'najaz_graphql');

        $helpers = __DIR__.'/../Http/helpers.php';

        if (file_exists($helpers)) {
            require_once $helpers;
        }

        if (isset($this->app->concord)) {
            $this->app->concord->registerModel(
                CitizenContract::class,
                Citizen::class
            );
        }
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/auth/guards.php',
            'auth.guards'
        );
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/auth/providers.php',
            'auth.providers'
        );
    }
}

