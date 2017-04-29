<?php

namespace Mini\Support\Facades;

/**
 * @see \Mini\Routing\Router
 */
class Route extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'router'; }

}
