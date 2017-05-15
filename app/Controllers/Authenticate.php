<?php
/**
 * Authenticate - A Controller for managing the User Authentication.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */

namespace App\Controllers;

use Mini\Foundation\Auth\AuthenticatesUsersTrait;
use Mini\Http\Request;
use Mini\Support\Facades\Redirect;

use App\Controllers\BaseController;


class Authenticate extends BaseController
{
	use AuthenticatesUsersTrait;

	//
	protected $layout = 'Authorize';

}
