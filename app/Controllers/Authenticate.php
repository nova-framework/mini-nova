<?php
/**
 * Authorize - A Controller for managing the User Authentication.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */

namespace App\Controllers;

use Mini\Foundation\Auth\AuthenticatesUsersTrait;
use Mini\Foundation\Auth\ThrottlesLoginsTrait;

use App\Controllers\BaseController;


class Authenticate extends BaseController
{
	use AuthenticatesUsersTrait, ThrottlesLoginsTrait;

	//
	protected $layout = 'Authorize';
}
