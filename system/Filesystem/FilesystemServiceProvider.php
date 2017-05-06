<?php

namespace Mini\Filesystem;

use Mini\Filesystem\Filesystem;
use Mini\Support\ServiceProvider;


class FilesystemServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bindShared('files', function()
        {
            return new Filesystem();
        });
    }

}
