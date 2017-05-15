<?php

namespace Mini\Support\Facades;


/**
 * @see \Mini\Foundation\Forge
 */
class Forge extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'Mini\Console\Contracts\KernelInterface'; }

}
