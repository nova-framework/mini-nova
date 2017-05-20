<?php
/**
 * Settings - Implements a simple Administration Settings.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */

namespace App\Controllers\Admin;

use Mini\Support\Facades\Auth;
use Mini\Support\Facades\Cache;
use Mini\Support\Facades\Config;
use Mini\Support\Facades\Event;
use Mini\Support\Facades\Input;
use Mini\Support\Facades\Redirect;
use Mini\Support\Facades\Validator;
use Mini\Support\Facades\View;

use App\Controllers\BackendController;
use App\Models\Option;


class Settings extends BackendController
{

	public function __construct()
	{
		parent::__construct();

		//
		$this->middleware('role:administrator');
	}

	protected function validator(array $data)
	{
		// Validation rules
		$rules = array(
			// The Application.
			'siteName'			=> 'required|max:100',

			// The Mailer
			'mailDriver'		=> 'required|alpha',
			'mailHost'			=> 'valid_host',
			'mailPort'			=> 'numeric',
			'mailFromAddress'	=> 'required|email',
			'mailFromName'		=> 'required|max:100',
			'mailEncryption'	=> 'alpha',
			'mailUsername'		=> 'max:100',
			'mailPassword'		=> 'max:100',
		);

		$messages = array(
			'valid_host' => __('The :attribute field is not a valid host.'),
		);

		$attributes = array(
			// The Application.
			'siteName'			=> __('Site Name'),
			'siteSkin'			=> __('Site Skin'),

			// The Mailer
			'mailDriver'		=> __('Mail Driver'),
			'mailHost'			=> __('Server Name'),
			'mailPort'			=> __('Server Port'),
			'mailFromAddress'	=> __('Mail from Adress'),
			'mailFromName'		=> __('Mail from Name'),
			'mailEncryption'	=> __('Encryption'),
			'mailUsername'		=> __('Server Username'),
			'mailPassword'		=> __('Server Password'),
		);

		// Add the custom Validation Rule commands.
		Validator::extend('valid_host', function($attribute, $value, $parameters)
		{
			return (filter_var($value, FILTER_VALIDATE_URL, ~FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED) !== false);
		});

		return Validator::make($data, $rules, $messages, $attributes);
	}

	public function index()
	{
		// Load the Options from database.
		$options = array(
			// The Application.
			'siteName' => Input::old('siteName', Config::get('app.name')),

			// The Mailer
			'mailDriver'		=> Input::old('mailDriver',	  		Config::get('mail.driver')),
			'mailHost'			=> Input::old('mailHost',			Config::get('mail.host')),
			'mailPort'			=> Input::old('mailPort',			Config::get('mail.port')),
			'mailFromAddress'	=> Input::old('mailFromAddress',	Config::get('mail.from.address')),
			'mailFromName'		=> Input::old('mailFromName',		Config::get('mail.from.name')),
			'mailEncryption'	=> Input::old('mailEncryption',		Config::get('mail.encryption')),
			'mailUsername'		=> Input::old('mailUsername',		Config::get('mail.username')),
			'mailPassword'		=> Input::old('mailPassword',		Config::get('mail.password')),
		);

		return $this->getView()
			->shares('title', __('Settings'))
			->withOptions($options);
	}

	public function store()
	{
		// Validate the Input data.
		$input = Input::only(
			'siteName',
			'mailDriver', 'mailHost', 'mailPort', 'mailFromAddress', 'mailFromName', 'mailEncryption', 'mailUsername', 'mailPassword'
		);

		$validator = $this->validator($input);

		if($validator->passes()) {
			// The Application.
			Option::set('app.name', $input['siteName']);

			// The Mailer
			Option::set('mail.driver',			$input['mailDriver']);
			Option::set('mail.host',			$input['mailHost']);
			Option::set('mail.port',			$input['mailPort']);
			Option::set('mail.from.address',	$input['mailFromAddress']);
			Option::set('mail.from.name',		$input['mailFromName']);
			Option::set('mail.encryption',		$input['mailEncryption']);
			Option::set('mail.username',		$input['mailUsername']);
			Option::set('mail.password',		$input['mailPassword']);

			// Invalidator the cached system options.
			Cache::forget('system_options');

			// Fire the associated Event.
			$user = Auth::user();

			Event::fire('app.modules.system.settings.updated', array($user, $input));

			// Prepare the flash message.
			$status = __('The Settings was successfully updated.');

			return Redirect::to('admin/settings')->with('success', $status);
		}

		// Errors occurred on Validation.
		return Redirect::back()->withInput()->withErrors($validator->errors());
	}

}
