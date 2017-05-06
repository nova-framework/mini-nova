<?php

namespace Mini\Language;

use Mini\Language\LanguageManager;
use Mini\Support\ServiceProvider;


class LanguageServiceProvider extends ServiceProvider
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
        $this->app->bindShared('language', function($app)
        {
            return new LanguageManager($app, $app['config']['app.locale']);
        });
    }

}
