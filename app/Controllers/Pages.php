<?php

namespace App\Controllers;

use Mini\Support\Facades\View;
use Mini\Support\Str;

use App\Controllers\BaseController;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class Pages extends BaseController
{
	/**
	 * The currently used Theme.
	 *
	 * @var string
	 */
	protected $theme = false; // Disable the usage of a Theme.

	/**
	 * The currently used Layout.
	 *
	 * @var string
	 */
	protected $layout = 'Static';


	public function display($slug = null)
	{
		$path = explode('/', $slug ?: 'home');

		//
		$page = $path[0];

		$subpage = isset($path[1]) ? $path[1] : null;

		$title = Str::title(
			str_replace(array('-', '_'), ' ', $subpage ?: $page)
		);

		// Calculate the current View name.
		array_unshift($path, 'pages');

		$view = implode('/', array_map(function ($value)
		{
			return Str::studly($value);

		}, $path));

		if (! View::exists($view)) {
			throw new NotFoundHttpException();
		}

		return View::make($view, compact('page', 'subpage'))
			->shares('title', $title);
	}
}
