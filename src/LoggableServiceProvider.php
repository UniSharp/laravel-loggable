<?php

namespace Unisharp\Loggable;

use Illuminate\Support\ServiceProvider;

/**
 * This is the auditing service provider class.
 *
 */
class LoggableServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot(\Illuminate\Routing\Router $router)
    {
        $this->setupMiddlewares($router);
    }

    /**
     * Setup the facades.
     *
     * @return void
     */
    protected function setupFacades()
    {
        \App::bind('Loggable', function()
        {
            return new \Unisharp\Loggable\Loggable;
        });
    }

    /**
     * Setup the middlewares.
     *
     * @return void
     */
    protected function setupMiddlewares($router)
    {
        $router->middleware('SetUserTrace', 'Unisharp\Loggable\Middlewares\SetUserTrace');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->setupFacades();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

}
