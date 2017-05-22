<?php

namespace Backend\Models;

use Mini\Database\ORM\Model as BaseModel;
use Mini\Support\Facades\Auth;
use Mini\Support\Facades\Config;
use Mini\Support\Facades\Session;

use Carbon\Carbon;


class OnlineUser extends BaseModel
{
	protected $hidden = array('payload');

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	public $table = 'online_users';

	protected $primaryKey = 'id';

	protected $fillable = array('session', 'user_id', 'ip', 'last_activity');

	public $timestamps = false;

	/**
	 * Returns the user that belongs to this entry.
	 */
	public function user()
	{
		return $this->belongsTo('Backend\Models\User', 'user_id', 'id');
	}

	/**
	 * Updates the session of the current user.
	 *
	 * @param  $query
	 * @return \Mini\Database\ORM\Builder
	 */
	public static function updateCurrent($request)
	{
		if (! Auth::check()) {
			// We track only the authenticated users.
			return;
		}

		$attributes = array(
			'session' => Session::getId(),
			'user_id' => Auth::id(),
		);

		static::updateOrCreate($attributes, array(
			'last_activity'	=> strtotime(Carbon::now()),
			'ip'			=> $request->ip()
		));
	}
}
