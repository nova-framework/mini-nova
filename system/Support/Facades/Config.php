<?php

namespace Mini\Support\Facades;

use Mini\Support\Facades\Facade;


/**
 * @see \Mini\Config\Repository
 */
class Config extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'config'; }

}
