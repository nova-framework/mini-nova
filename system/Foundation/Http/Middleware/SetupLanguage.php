<?php

namespace Mini\Foundation\Http\Middleware;

use Mini\Foundation\Application;

use Closure;


class SetupLanguage
{
	/**
	 * The application implementation.
	 *
	 * @var \Mini\Foundation\Application
	 */
	protected $app;

	/**
	 * Create a new middleware instance.
	 *
	 * @param  \Mini\Foundation\Application  $app
	 * @return void
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
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
		$session = $this->app['session.store'];

		if (! $session->has('language')) {
			$cookie = $request->cookie(PREFIX .'language', null);

			$locale = $cookie ?: $this->app['config']->get('app.locale');

			$session->set('language', $locale);
		} else {
			$locale = $session->get('language');
		}

		$this->app['language']->setLocale($locale);

		return $next($request);
	}

}
