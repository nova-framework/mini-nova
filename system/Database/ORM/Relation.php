<?php

namespace Mini\Database\ORM;

use Mini\Database\ORM\Builder;
use Mini\Database\ORM\Model;


class Relation
{
	protected $query;

	protected $parent;

	protected $getter;

	/**
	 * All of the registered relation macros.
	 *
	 * @var array
	 */
	protected $macros = array();


	/**
	 * Create a new relation instance.
	 *
	 * @param  \Mini\Database\ORM\Builder  $query
	 * @param  \Mini\Database\ORM\Model  $parent
	 * @param string $getter
	 * @return void
	 */
	public function __construct(Builder $query, Model $parent, $getter = 'first')
	{
		$this->query = $query;

		$this->parent = $parent;

		$this->getter = $getter;
	}

	/**
	 * Get the result(s) of the relationship.
	 *
	 * @return mixed
	 */
	public function getResults()
	{
		return call_user_func(array($this->query, $this->getter));
	}

	/**
	 * Extend the relation with a given callback.
	 *
	 * @param  string	$name
	 * @param  \Closure  $callback
	 * @return void
	 */
	public function macro($name, Closure $callback)
	{
		$this->macros[$name] = $callback;
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
		if (isset($this->macros[$method])) {
			array_unshift($parameters, $this);

			return call_user_func_array($this->macros[$method], $parameters);
		}

		return call_user_func_array(array($this->query, $method), $parameters);
	}
}
