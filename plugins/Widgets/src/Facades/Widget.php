<?php

namespace Widgets\Facades;

use Mini\Support\Facades\Facade;


class Widget extends BaseFacade
{

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'widgets'; }
}
