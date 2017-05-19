<?php

namespace Mini\Foundation\Support\Providers;

use Mini\Auth\Contracts\Access\GateInterface as GateContract;

use Mini\Support\ServiceProvider;


class AuthServiceProvider extends ServiceProvider
{
	/**
	 * The policy mappings for the application.
	 *
	 * @var array
	 */
	protected $policies = array();


	/**
	 * Register the application's policies.
	 *
	 * @param  \Mini\Contracts\Auth\Access\Gate  $gate
	 * @return void
	 */
	public function registerPolicies(GateContract $gate)
	{
		foreach ($this->policies as $key => $value) {
			$gate->policy($key, $value);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		//
	}
}
