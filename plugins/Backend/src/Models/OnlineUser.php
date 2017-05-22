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

	protected $dates = array('last_activity');

	public $timestamps = false;

	/**
	 * Returns the user that belongs to this entry.
	 */
	public function user()
	{
		return $this->belongsTo('Backend\Models\User');
	}

	/**
	 * Updates the session of the current user.
	 *
	 * @param  $query
	 * @return \Mini\Database\ORM\Builder
	 */
	public static function updateCurrent($request)
	{
		$id = Session::getId();

		$userId = Auth::check() ? Auth::id() : null;

		$attributes = array(
			'session' => $id,
			'user_id' => $userId,
		);

		static::updateOrCreate($attributes, array(
			'last_activity'	=> Carbon::now(),
			'ip'			=> $request->ip()
		));
	}

	/**
	 * Returns all the guest users.
	 *
	 * @param  $query
	 * @return \Mini\Database\ORM\Builder
	 */
	public function scopeGuests($query)
	{
		$limit = Config::get('backend.activity_limit');

		return $query->whereNull('user_id')
			->where('last_activity', '>=', strtotime(Carbon::now()->subMinutes($limit)));
	}

	/**
	 * Returns all the registered users.
	 *
	 * @param  $query
	 * @return \Mini\Database\ORM\Builder
	 */
	public function scopeRegistered($query)
	{
		$limit = Config::get('backend.activity_limit');

		return $query->whereNotNull('user_id')
			->where('last_activity', '>=', strtotime(Carbon::now()->subMinutes($limit)))
			->with('user');
	}
}
