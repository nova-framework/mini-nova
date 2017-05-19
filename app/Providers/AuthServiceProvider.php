<?php

namespace App\Providers;

use Mini\Auth\Contracts\Access\GateInterface as Gate;
use Mini\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;


class AuthServiceProvider extends ServiceProvider
{
	/**
	 * The policy mappings for the application.
	 *
	 * @var array
	 */
	protected $policies = array(
		'App\Models\SomeModel' => 'App\Policies\ModelPolicy',
	);


	/**
	 * Register any application authentication / authorization services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$gate = $this->app->make(Gate::class);

		$this->registerPolicies($gate);

		//
	}
}
