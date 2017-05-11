<?php

namespace Mini\View\Middleware;

use Mini\Support\ViewErrorBag;
use Mini\View\Factory as ViewFactory;

use Closure;


class ShareErrorsFromSession
{
	/**
	 * The view factory implementation.
	 *
	 * @var \Mini\View\Factory
	 */
	protected $view;

	/**
	 * Create a new error binder instance.
	 *
	 * @param  \Mini\View\Factory  $view
	 * @return void
	 */
	public function __construct(ViewFactory $view)
	{
		$this->view = $view;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$errors = $request->session()->get('errors', new ViewErrorBag());

		$this->view->share('errors', $errors);

		return $next($request);
	}
}
