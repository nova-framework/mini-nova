<?php

namespace Mini\Support\Facades;

use Mini\Support\Facades\Facade;


/**
 * @see \Mini\Pagination\Factory
 */
class Paginator extends Facade
{
	/**
	 * Return the Application instance.
	 *
	 * @return \Mini\Pagination\Factory
	 */
	public static function instance()
	{
		$accessor = static::getFacadeAccessor();

		return static::resolveFacadeInstance($accessor);
	}

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'paginator'; }

}
