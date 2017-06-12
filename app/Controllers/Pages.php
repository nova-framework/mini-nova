<?php

namespace App\Controllers;

use Mini\Support\Facades\Redirect;
use Mini\Support\Facades\Config;
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
		$segments = explode('/', $slug ?: 'home');

		// Compute the page and subpage variables.
		$page = $segments[0];

		$subpage = isset($segments[1]) ? $segments[1] : null;

		// Calculate the current view.
		$view = implode('/', array_map(function ($value)
		{
			return Str::studly($value);

		}, $segments));

		if (! View::exists($view = $this->getViewName($view))) {
			throw new NotFoundHttpException();
		}

		$title = Str::title(
			str_replace(array('-', '_'), ' ', $subpage ?: $page)
		);

		return View::make($view, compact('page', 'subpage'))
			->shares('title', $title);
	}
}
