<?php
/**
 * Dasboard - Implements a simple Administration Dashboard.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 */

namespace Backend\Controllers\Admin;

use Mini\Support\Facades\Config;
use Mini\Support\Facades\Input;
use Mini\Support\Facades\Language;
use Mini\Support\Facades\Response;

use Backend\Controllers\BaseController;
use Backend\Models\OnlineUser;
use Backend\Models\User;

use Carbon\Carbon;


class Dashboard extends BaseController
{

	public function data()
	{
		$format = __d('backend', '%d %b %Y, %H:%M');

		$columns = array(
			array('data' => 'userid',   'field' => 'id'),
			array('data' => 'username', 'field' => 'username'),

			array('data' => 'role', 'field' => 'role_id', 'uses' => function($user)
			{
				return $user->role->name;
			}),

			array('data' => 'first_name',	'field' => 'first_name'),
			array('data' => 'last_name',	'field' => 'last_name'),
			array('data' => 'email',		'field' => 'email'),

			array('data' => 'date', 'uses' => function($user) use ($format)
			{
				$online = $user->online->first();

				return Carbon::createFromTimestamp($online->last_activity)
					->formatLocalized($format);
			}),

			array('data' => 'actions', 'uses' => function($online)
			{
				return '-';
			}),
		);

		$input = Input::only('draw', 'columns', 'start', 'length', 'search', 'order');

		//
		$activityLimit = Config::get('backend::activityLimit');

		$since = Carbon::now()->subMinutes($activityLimit)->timestamp;

		$query = User::with('role')->activeSince($since);

		//
		$data = $this->dataTable($query, $input, $columns);

		return Response::json($data);
	}

	public function index()
	{
		$content = '';

		//
		$langInfo = Language::info();

		return $this->getView()
			->shares('title', __d('backend', 'Dashboard'))
			->with('langInfo', $langInfo)
			->with('debug', $content);
	}

}
