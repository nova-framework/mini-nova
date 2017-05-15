<?php

namespace Mini\Foundation\Auth;

use Mini\Auth\UserTrait;
use Mini\Auth\Contracts\UserInterface;
use Mini\Database\ORM\Model;


class User extends Model implements UserInterface
{
	use UserTrait;
}
