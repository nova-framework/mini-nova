<?php

namespace Taxonomy\Support\Facades;

use Mini\Support\Facades\Facade;


class Taxonomy extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
    */
    protected static function getFacadeAccessor() { return 'taxonomy'; }
}
