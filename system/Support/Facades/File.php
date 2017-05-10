<?php

namespace Mini\Support\Facades;

/**
 * @see \Mini\Filesystem\Filesystem
 */
class File extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'files'; }

}
