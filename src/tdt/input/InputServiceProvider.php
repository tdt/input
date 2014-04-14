<?php

namespace Tdt\Input;

use Illuminate\Support\ServiceProvider;
use Tdt\Input\Commands\Import;
use Tdt\Input\Commands\Export;
use Tdt\Input\Commands\ExecuteJob;

class InputServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('tdt/input');

        $this->app['input.execute'] = $this->app->share(function ($app) {
            return new ExecuteJob();
        });

        $this->app['input.export'] = $this->app->share(function ($app) {
            return new Export();
        });

        $this->app['input.import'] = $this->app->share(function ($app) {
            return new Import();
        });

        $this->commands('input.export');
        $this->commands('input.execute');
        $this->commands('input.import');

        include __DIR__ . '/../../routes.php';
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }
}
