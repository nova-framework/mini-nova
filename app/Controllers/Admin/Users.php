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
        $user = User::find(4);

        $content .= '<pre>' .htmlentities(var_export($user, true)) .'</pre>';

        //
        $role = $user->role;

        $content .= '<pre>' .htmlentities(var_export($role, true)) .'</pre>';

        //
        $users = $role->users()->take(15)->orderBy('username')->get();

        $content .= '<pre>' .htmlentities(var_export($users, true)) .'</pre>';

        //
        //$users = User::all();

        //$content .= '<pre>' .htmlentities(var_export($users, true)) .'</pre>';

        return View::make('Default')
            ->shares('title', 'Users')
            ->with('content', $content);
    }
}

