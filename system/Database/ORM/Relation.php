<?php

namespace Mini\Database\ORM;

use Mini\Database\ORM\Builder;


class Relation
{
	protected $query;

	protected $getter;


	public function __construct(Builder $query, $getter = 'first')
	{
		$this->query = $query;

		$this->getter = $getter;
	}

	public function getResults()
	{
		return call_user_func(array($this->query, $this->getter));
	}

	/**
	 * Handle dynamic method calls into the method.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array(array($this->query, $method), $parameters);
	}
}
