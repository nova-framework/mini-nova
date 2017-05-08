<?php

namespace Mini\Database\ORM;

use Mini\Database\ORM\Builder;
use Mini\Database\ORM\Model;
use Mini\Support\Collection;
use Mini\Support\Arr;

use Closure;


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
	 * Whether or not we return a single result.
	 *
	 * @var bool
	 */
	protected $single;

	/**
	 * All of the registered relation macros.
	 *
	 * @var array
	 */
	protected $macros = array();

	/**
	 * Indicates if the relation is adding constraints.
	 *
	 * @var bool
	 */
	protected static $constraints = true;


	/**
	 * Create a new relation instance.
	 *
	 * @param  \Mini\Database\ORM\Model  $related
	 * @param  \Mini\Database\ORM\Model  $parent
	 * @param string $getter
	 * @return void
	 */
	public function __construct(Model $related, Model $parent, $single = true)
	{
		$this->related = $related;

		$this->parent = $parent;

		$this->single = $single;
	}

	/**
	 * Initialize the relation on a set of models.
	 *
	 * @param  array   $models
	 * @param  string  $relation
	 * @return array
	 */
	public function initRelation(array $models, $relation)
	{
		foreach ($models as $model) {
			$model->setRelation($relation, $this->single ? null : $this->related->newCollection());
		}

		return $models;
	}

	/**
	 * Set the base constraints on the relation query.
	 *
	 * @return void
	 */
	public function addConstraints()
	{
		$query = $this->related->newQuery();

		return $this->query = call_user_func($this->macros['constraints'], $this, $query);
	}

	/**
	 * Set the constraints for an eager load of the relation.
	 *
	 * @param  array  $models
	 * @return void
	 */
	public function addEagerConstraints(array $models)
	{
		$query = $this->related->newQuery();

		return $this->query = call_user_func($this->macros['eagerConstraints'], $this, $query, $models);
	}

	/**
	 * Match the eagerly loaded results to their parents.
	 *
	 * @param  array   $models
	 * @param  \Nova\Database\ORM\Collection  $results
	 * @param  string  $relation
	 * @return array
	 */
	public function match(array $models, Collection $results, $relation)
	{
		return call_user_func($this->macros['match'], $this, $models, $results, $relation);
	}

	/**
	 * Get the result(s) of the relationship.
	 *
	 * @return mixed
	 */
	public function getResults()
	{
		$method = $this->single ? 'first' : 'get';

		return call_user_func(array($this->query, $method));
	}

	public function getEager()
	{
		return $this->query->get();
	}

	/**
	 * Run a callback with constraints disabled on the relation.
	 *
	 * @param  \Closure  $callback
	 * @return mixed
	 */
	public static function noConstraints(Closure $callback)
	{
		static::$constraints = false;

		$results = call_user_func($callback);

		static::$constraints = true;

		return $results;
	}

	/**
	 * Get all of the primary keys for an array of models.
	 *
	 * @param  array   $models
	 * @param  string  $key
	 * @return array
	 */
	public function getKeys(array $models, $key = null)
	{
		return array_unique(array_values(array_map(function($value) use ($key)
		{
			return ! is_null($key) ? $value->getAttribute($key) : $value->getKey();

		}, $models)));
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function getRelated()
	{
		return $this->related;
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
		$this->addConstraints();

		return call_user_func_array(array($this->query, $method), $parameters);
	}
}
