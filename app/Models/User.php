<?php

namespace App\Models;

use Mini\Auth\UserTrait;
use Mini\Auth\Contracts\UserInterface;
use Mini\Database\ORM\Model as BaseModel;


class User extends BaseModel implements UserInterface
{
	use UserTrait;

	//
	protected $table = 'users';

	protected $primaryKey = 'id';

	protected $fillable = array('role_id', 'username', 'password', 'realname', 'email', 'image', 'activation_code');

	protected $hidden = array('password', 'remember_token');


	public function role()
	{
		return $this->belongsTo('App\Models\Role', 'role_id', 'id', 'role');
	}

	public function hasRole($roles, $strict = false)
	{
		$this->load('role');

		$slug = strtolower($this->role->slug);

		// Check if the User has a Root role.
		if (($slug === 'root') && ! $strict) {
			return true;
		}

		foreach ((array) $roles as $role) {
			if (strtolower($role) == $role) {
				return true;
			}
		}

		return false;
	}

	public function messages()
	{
		return $this->hasMany('App\Models\Message', 'sender_id', 'id');
	}

	public function name() {
		return trim($this->first_name .' ' .$this->last_name);
	}
}
