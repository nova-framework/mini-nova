<?php

namespace Mini\Foundation\Bootstrap;

use Mini\Http\Request;
use Mini\Foundation\Application;


class SetRequestForConsole
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Mini\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $url = $app->make('config')->get('app.url', 'http://localhost');

        $app->instance('request', Request::create($url, 'GET', array(), array(), array(), $_SERVER));
    }
}
