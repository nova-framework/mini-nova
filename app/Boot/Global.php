<?php

//--------------------------------------------------------------------------
// Load The Options
//--------------------------------------------------------------------------

use App\Models\Option;

if (CONFIG_STORE === 'database') {
	// Retrieve the Option items, caching them for 24 hours.
	$options = Cache::remember('system_options', 1440, function()
	{
		return Option::all();
	});

	foreach ($options as $option) {
		$key = $option->group;

		if (! empty($option->item)) {
			$key .= '.' .$option->item;
		}

		Config::set($key, $option->value);
	}
} else if(CONFIG_STORE !== 'files') {
	throw new InvalidArgumentException('Invalid Config Store type.');
}

//--------------------------------------------------------------------------
// Boot Stage Customization
//--------------------------------------------------------------------------
