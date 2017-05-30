<?php

namespace Content\Providers;

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
        'Content\Models\SomeModel' => 'Content\Policies\ModelPolicy',
    );


    /**
     * Register any application authentication / authorization services.
     *
     * @param  Mini\Auth\Contracts\Access\GateInterface  $gate
     * @return void
     */
    public function boot(Gate $gate)
    {
        $this->registerPolicies($gate);

        //
    }
}
