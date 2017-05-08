<?php

namespace App\Controllers\Admin;

use Mini\Support\Facades\View;

use App\Core\Controller;
use App\Models\User;


class Users extends Controller
{

    public function index()
    {
        $content = '';

        //
        $user = User::find(1);

        $content .= '<pre>' .htmlentities(var_export($user, true)) .'</pre>';

        //
        $users = User::all();

        $content .= '<pre>' .htmlentities(var_export($users, true)) .'</pre>';

        return View::make('Default')
            ->shares('title', 'Users')
            ->with('content', $content);
    }
}

