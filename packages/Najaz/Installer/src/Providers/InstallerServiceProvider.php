<?php

namespace Najaz\Installer\Providers;

use Illuminate\Support\ServiceProvider;
use Najaz\Installer\Console\Commands\SeedDefaultData;

class InstallerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCommands();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the console commands of this package.
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SeedDefaultData::class,
            ]);
        }
    }
}

