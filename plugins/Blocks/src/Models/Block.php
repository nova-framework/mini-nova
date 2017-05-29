<?php

namespace Blocks\Models;

use Mini\Database\ORM\Model as BaseModel;


class Block extends BaseModel
{
	protected $table = 'blocks';

	protected $primaryKey = 'id';

	protected $fillable = array(
		'name', 'slug', 'title', 'content', 'area', 'weight', 'paths', 'paths_mode', 'auth_mode', 'user_roles', 'hide_title', 'handler'
	);

	protected $hidden = array(
		'created_at','updated_at'
	);
}
