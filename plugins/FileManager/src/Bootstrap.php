<?php

/*
|--------------------------------------------------------------------------
| Plugin Bootstrap
|--------------------------------------------------------------------------
|
| Here is where you can register all of the Bootstrap for the Plugin.
*/


/**
 * Listener Closure to the Event 'backend.menu'.
 */
Event::listen('backend.menu', function($user)
{
	if (! $user->hasRole('administrator')) {
		return array();
	}

	$items = array(
		array(
			'uri'		=> 'admin/files',
			'title'		=> __d('files', 'Files'),
			'label'		=> '',
			'icon'		=> 'file',
			'weight'	=> 3,
		),
	);

	return $items;
});
