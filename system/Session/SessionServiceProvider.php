<?php
/**
 * SessionServiceProvider - Implements a Service Provider for Session.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */

namespace Mini\Session;

use Mini\Session\Store as SessionStore;
use Mini\Support\ServiceProvider;


class SessionServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the Service Provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bindShared('session.store', function($app)
        {
            $name = $app['config']->get('session.cookie');

            return new SessionStore($name);
        });
    }

}

