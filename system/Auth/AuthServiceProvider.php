<?php

namespace Mini\Auth;

use Mini\Auth\AuthManager;
use Mini\Support\ServiceProvider;


class AuthServiceProvider extends ServiceProvider
{

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerAuthenticator();

		$this->registerUserResolver();

		$this->registerRequestRebindHandler();
	}

	/**
	 * Register the authenticator services.
	 *
	 * @return void
	 */
	protected function registerAuthenticator()
	{
		$this->app->singleton('auth', function ($app)
		{
			$app['auth.loaded'] = true;

			return new AuthManager($app);
		});

		$this->app->singleton('auth.driver', function ($app)
		{
			return $app['auth']->guard();
		});
	}

	/**
	 * Register a resolver for the authenticated user.
	 *
	 * @return void
	 */
	protected function registerUserResolver()
	{
		$this->app->bind('Mini\Auth\Contracts\UserInterface', function ($app)
		{
			$callback = $app['auth']->userResolver();

			return call_user_func($callback);
		});
	}

	/**
	 * Register a resolver for the authenticated user.
	 *
	 * @return void
	 */
	protected function registerRequestRebindHandler()
	{
		$this->app->rebinding('request', function ($app, $request)
		{
			$request->setUserResolver(function ($guard = null) use ($app)
			{
				$callback = $app['auth']->userResolver();

				return call_user_func($callback, $guard);
			});
		});
	}

}
