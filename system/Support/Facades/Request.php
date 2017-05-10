<?php

namespace Mini\Support\Facades;

use Mini\Http\Request as HttpRequest;
use Mini\Support\Facades\Facade;

use ReflectionMethod;
use ReflectionException;


/**
 * @see \Mini\Http\Request
 */
class Request extends Facade
{
	/**
	 * Return the Application instance.
	 *
	 * @return \Mini\Http\Request
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
	protected static function getFacadeAccessor() { return 'request'; }

}
