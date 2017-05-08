<?php

namespace Mini\Database\ORM;

use Mini\Database\ORM\Builder;
use Mini\Database\ORM\Model;
use Mini\Support\Arr;


class Relation
{
	/**
	 * The ORM query builder instance.
	 *
	 * @var \Mini\Database\ORM\Builder
	 */
	protected $query;

	/**
	 * The parent model instance.
	 *
	 * @var \Mini\Database\ORM\Model
	 */
	protected $parent;

	/**
	 * The related model instance.
	 *
	 * @var \Mini\Database\ORM\Model
	 */
	protected $related;

	/**
	 * The getter name for returning results.
	 *
	 * @var string
	 */
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

		$this->related = $query->getModel();

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
	 * Get all of the primary keys for an array of models.
	 *
	 * @param  array   $models
	 * @param  string  $key
	 * @return array
	 */
	protected function getKeys(array $models, $key = null)
	{
		return Arr::unique(array_values(array_map(function($value) use ($key)
		{
			return ! is_null($key) ? $value->getAttribute($key) : $value->getKey();

		}, $models)));
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
