<?php

namespace App\Controllers\Admin;

use Mini\Support\Facades\View;

use App\Core\Controller;
use App\Models\Role;
use App\Models\User;

class Users extends Controller
{

    public function index()
    {
        $content = '';

        //
        $user = User::with('role')->find(4);

        //
        $role = $user->role;

        $content .= '<pre>' .htmlentities(var_export($user, true)) .'</pre>';

        $content .= '<pre>' .htmlentities(var_export($role, true)) .'</pre>';

        $content .= '<pre>' .htmlentities(var_export($user->toArray(), true)) .'</pre>';

        //
        $roles = Role::with('users')->get();

        /*
        foreach ($roles as $role) {
			$content .= '<br><pre>' .htmlentities(var_export($role->id, true)) .'</pre>';

			$users = $role->users->lists('username');

			$content .= '<pre>' .htmlentities(var_export($users, true)) .'</pre>';
        }
        */

        $content .= '<pre>' .htmlentities(var_export($roles, true)) .'</pre>';

        //
        //$users = User::all();

        //$content .= '<pre>' .htmlentities(var_export($users, true)) .'</pre>';

        return View::make('Default')
            ->shares('title', 'Users')
            ->with('content', $content);
    }
}

