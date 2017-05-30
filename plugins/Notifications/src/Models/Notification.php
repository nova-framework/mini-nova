<?php

namespace Notifications\Models;

use Mini\Database\ORM\Model as BaseModel;
use Mini\Database\ORM\ModelNotFoundException;
use Mini\Support\Contracts\ArrayableInterface;

use Notifications\Database\ORM\Collection;


class Notification extends BaseModel
{
	protected $table = 'notifications';

	protected $primaryKey = 'id';

	protected $fillable = array(
		'type', 'notifiable_id', 'notifiable_type', 'data', 'read_at'
	);

	protected $dates = array('read_at');

	/**
	 * Get the notifiable entity that the notification belongs to.
	 */
	public function notifiable()
	{
		return $this->morphTo();
	}

	public function getDataAttribute($value)
	{
		return json_decode($value, true);
	}

	public function setDataAttribute($value)
	{
		if ($value instanceof ArrayableInterface) {
			$value = $value->toArray();
		}

		$this->attributes['data'] = jsone_encode($value);
	}

	/**
	 * Mark the notification as read.
	 *
	 * @return void
	 */
	public function markAsRead()
	{
		if (is_null($this->read_at)) {
			$this->forceFill(array(
				'read_at' => $this->freshTimestamp()
			));

			$this->save();
		}
	}

	/**
	 * Determine if a notification has been read.
	 *
	 * @return bool
	 */
	public function read()
	{
		return $this->read_at !== null;
	}

	/**
	 * Determine if a notification has not been read.
	 *
	 * @return bool
	 */
	public function unread()
	{
		return $this->read_at === null;
	}

	/**
	 * Create a new database notification collection instance.
	 *
	 * @param  array  $models
	 * @return \Backend\Notifications\Collection
	 */
	public function newCollection(array $models = array())
	{
		return new Collection($models);
	}
}
