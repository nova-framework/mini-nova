<?php

namespace App\Models;

use Mini\Database\Model;


class User extends Model
{
    protected $table = 'users';

    protected $primaryKey = 'id';

    protected $hidden = array('password');
}
