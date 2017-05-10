<?php

namespace App\Models;

use Mini\Database\ORM\Model as BaseModel;


class User extends BaseModel
{
	protected $table = 'users';

	protected $primaryKey = 'id';

	protected $fillable = array('role_id', 'username', 'password', 'realname', 'email', 'image', 'activation_code');

	protected $hidden = array('password', 'remember_token');


	public function role()
	{
		return $this->belongsTo('App\Models\Role', 'role_id', 'id', 'role');
	}
}
