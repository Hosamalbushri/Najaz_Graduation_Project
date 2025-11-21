<?php

namespace Najaz\GraphQLAPI\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Najaz\Citizen\Contracts\Citizen as CitizenContract;
use Najaz\GraphQLAPI\Models\Citizen\Citizen;
use Najaz\GraphQLAPI\NajazGraphql;
use Najaz\GraphQLAPI\Facades\NajazGraphql as NajazGraphqlFacade;

class GraphQLAPIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerFacades();

        $this->registerConfig();
    }

    /**
     * Register facades.
     */
    protected function registerFacades(): void
    {
        $loader = AliasLoader::getInstance();

        $loader->alias('najaz_graphql', NajazGraphqlFacade::class);

        $this->app->singleton('najaz_graphql', function () {
            return app()->make(NajazGraphql::class);
        });
    }

    /**
     * Register package config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/auth/guards.php',
            'auth.guards'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/auth/providers.php',
            'auth.providers'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $helpers = __DIR__.'/../Http/helpers.php';

        if (file_exists($helpers)) {
            require_once $helpers;
        }

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'najaz_graphql');

        $this->overrideCoreClasses();

        $this->publishesDefault();
    }

    /**
     * Publish the default configuration files.
     */
    protected function publishesDefault(): void
    {
        $this->publishes([
            __DIR__.'/../Config/lighthouse.php' => config_path('lighthouse.php'),
        ], ['najaz-api-lighthouse']);

        $this->publishes([
            __DIR__.'/../Config/graphiql.php' => config_path('graphiql.php'),
        ], ['najaz-api-graphiql']);

        $this->publishes([
            __DIR__.'/../Config/schema.graphql' => base_path('graphql/schema.graphql'),
        ], ['najaz-api-graphql-schema']);
    }

    /**
     * Override the core classes
     */
    protected function overrideCoreClasses(): void
    {
        if (isset($this->app->concord)) {
            $this->app->concord->registerModel(
                CitizenContract::class,
                Citizen::class
            );
        }
    }
}

