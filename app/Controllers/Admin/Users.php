<?php
/**
 * Users - A Controller for managing the Users Authentication.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 */

namespace App\Controllers\Admin;

use Mini\Database\ORM\ModelNotFoundException;
use Mini\Support\Facades\Auth;
use Mini\Support\Facades\Hash;
use Mini\Support\Facades\Input;
use Mini\Support\Facades\File;
use Mini\Support\Facades\Language;
use Mini\Support\Facades\Redirect;
use Mini\Support\Facades\Response;
use Mini\Support\Facades\Session;
use Mini\Support\Facades\Validator;
use Mini\Support\Facades\View;

use App\Controllers\Admin\BaseController;
use App\Models\User;
use App\Models\Role;

use Carbon\Carbon;


class Users extends BaseController
{

	public function __construct()
	{
		$this->middleware('role:administrator');
	}

	protected function validator(array $data, $id = null)
	{
		if (! is_null($id)) {
			$ignore = ',' .intval($id);

			$required = 'sometimes|required';
		} else {
			$ignore = '';

			$required = 'required';
		}

		// The Validation rules.
		$rules = array(
			'username'			  => 'required|min:4|max:100|alpha_dash|unique:users,username' .$ignore,
			'role'				  => 'required|numeric|exists:roles,id',
			'first_name'			=> 'required|min:4|max:100|valid_name',
			'last_name'			 => 'required|min:4|max:100|valid_name',
			'location'			  => 'min:2|max:100|valid_location',
			'password'			  => $required .'|confirmed|strong_password',
			'password_confirmation' => $required .'|same:password',
			'email'				 => 'required|min:5|max:100|email|unique:users,email' .$ignore,
			'image'				 => 'max:1024|mimes:png,jpeg,jpg,gif',
		);

		$messages = array(
			'valid_name'	  => __('The :attribute field is not a valid name.'),
			'valid_location'  => __('The :attribute field is not a valid location.'),
			'strong_password' => __('The :attribute field is not strong enough.'),
		);

		$attributes = array(
			'username'			  => __('Username'),
			'role'				  => __('Role'),
			'first_name'			=> __('First Name'),
			'last_name'			 => __('Last Name'),
			'location'			  => __('Location'),
			'password'			  => __('Password'),
			'password_confirmation' => __('Password confirmation'),
			'email'				 => __('E-mail'),
			'image'				 => __('Profile Picture'),
		);

		// Add the custom Validation Rule commands.
		Validator::extend('valid_name', function($attribute, $value, $parameters)
		{
			$pattern = '~^(?:[\p{L}\p{Mn}\p{Pd}\'\x{2019}]+(?:$|\s+)){1,}$~u';

			return (preg_match($pattern, $value) === 1);
		});

		Validator::extend('valid_location', function($attribute, $value, $parameters)
		{
			$pattern = '~^(?:[\p{L}\p{Mn}\p{Pd}\',\x{2019}]+(?:$|\s+)){1,}$~u';

			return (preg_match($pattern, $value) === 1);
		});

		Validator::extend('strong_password', function($attribute, $value, $parameters)
		{
			$pattern = "/(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/";

			return (preg_match($pattern, $value) === 1);
		});

		return Validator::make($data, $rules, $messages, $attributes);
	}

	public function data()
	{
		$columns = array(
			array('data' => 'userid',   'field' => 'id'),
			array('data' => 'username', 'field' => 'username'),
			array('data' => 'name',	 'field' => 'first_name'),
			array('data' => 'surname',  'field' => 'last_name'),
			array('data' => 'email',	'field' => 'email'),

			array('data' => 'role', 'field' => 'role_id', 'uses' => function($user)
			{
				return $user->role->name;
			}),

			array('data' => 'date', 'field' => 'created_at', 'uses' => function($user)
			{
				$format = __('%d %b %Y, %H:%M');

				return $user->created_at->formatLocalized($format);
			}),

			array('data' => 'actions', 'uses' => function($user)
			{
				return View::make('Partials/UsersTableActions', array(), 'Users')
					->with('user', $user)
					->render();
			}),
		);

		$input = Input::only('draw', 'columns', 'start', 'length', 'search', 'order');

		$query = User::with('role');

		//
		$data = $this->dataTable($query, $input, $columns);

		return Response::json($data);
	}

	public function index()
	{
		$langInfo = Language::info();

		return $this->getView()
			->shares('title', __('Users'))
			->with('langInfo', $langInfo);
	}

	public function create()
	{
		// Get all available User Roles.
		$roles = Role::all();

		return $this->getView()
			->shares('title', __('Create User'))
			->with('roles', $roles);
	}

	public function store()
	{
		// Validate the Input data.
		$input = Input::only('username', 'role', 'first_name', 'last_name', 'location', 'password', 'password_confirmation', 'email', 'image');

		if (empty($input['location'])) unset($input['location']);

		//
		$validator = $this->validator($input);

		if($validator->passes()) {
			// Encrypt the given Password.
			$password = Hash::make($input['password']);

			// Create a User Model instance.
			$user = new User();

			//
			$user->username   = $input['username'];
			$user->password   = $password;
			$user->role_id	= $input['role'];
			$user->first_name = $input['first_name'];
			$user->last_name  = $input['last_name'];
			$user->email	  = $input['email'];

			// Save the User information.
			$user->save();

			// Prepare the flash message.
			$status = __('The User <b>{0}</b> was successfully created.', $input['username']);

			return Redirect::to('admin/users')->with('success', $status);
		}

		// Errors occurred on Validation.
		return Redirect::back()->withInput()->withErrors($validator->errors());
	}

	public function show($id)
	{
		// Get the User Model instance.
		try {
			$user = User::findOrFail($id);
		}
		catch (ModelNotFoundException $e) {
			$status = __('The User with ID: {0} was not found.', $id);

			return Redirect::to('admin/users')->with('warning', $status);
		}

		return $this->getView()
			->shares('title', __('Show User'))
			->with('user', $user);
	}

	public function edit($id)
	{
		// Get the User Model instance.
		try {
			$user = User::findOrFail($id);
		}
		catch (ModelNotFoundException $e) {
			$status = __('The User with ID: {0} was not found.', $id);

			return Redirect::to('admin/users')->with('warning', $status);
		}

		// Get all available User Roles.
		$roles = Role::all();

		return $this->getView()
			->shares('title', __('Edit User'))
			->with('roles', $roles)
			->with('user', $user);
	}

	public function update($id)
	{
		// Get the User Model instance.
		try {
			$user = User::findOrFail($id);
		}
		catch (ModelNotFoundException $e) {
			$status = __('The User with ID: {0} was not found.', $id);

			return Redirect::to('admin/users')->with('warning', $status);
		}

		// Validate the Input data.
		$input = Input::only('username', 'role', 'first_name', 'last_name', 'location', 'password', 'password_confirmation', 'email', 'image');

		if (empty($input['location'])) unset($input['location']);

		if(empty($input['password']) && empty($input['password_confirm'])) {
			unset($input['password']);
			unset($input['password_confirmation']);
		}

		$validator = $this->validator($input, $id);

		if($validator->passes()) {
			$origName = $user->username;

			// Update the User Model instance.
			$user->username   = $input['username'];
			$user->role_id	= $input['role'];
			$user->first_name = $input['first_name'];
			$user->last_name  = $input['last_name'];
			$user->email	  = $input['email'];

			if(isset($input['password'])) {
				// Encrypt and add the given Password.
				$user->password = Hash::make($input['password']);
			}

			// Save the User information.
			$user->save();

			// Prepare the flash message.
			$status = __('The User <b>{0}</b> was successfully updated.', $origName);

			return Redirect::to('admin/users')->with('success', $status);
		}

		// Errors occurred on Validation.
		return Redirect::back()->withInput()->withErrors($validator->errors());
	}

	public function destroy($id)
	{
		// Get the User Model instance.
		try {
			$user = User::findOrFail($id);
		}
		catch (ModelNotFoundException $e) {
			$status = __('The User with ID: {0} was not found.', $id);

			return Redirect::to('admin/users')->with('warning', $status);
		}

		// Destroy the requested User record.
		$user->delete();

		// Prepare the flash message.
		$status = __('The User <b>{0}</b> was successfully deleted.', $user->username);

		return Redirect::to('admin/users')->with('success', $status);
	}

}
