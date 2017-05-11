<?php

namespace Mini\View\Middleware;

use Mini\Support\ViewErrorBag;
use Mini\Support\Facades\View;

use Closure;


class ShareErrorsFromSession
{

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$errors = $request->session()->pull('errors', new ViewErrorBag());

		View::share('errors', $errors);

		return $next($request);
	}
}
