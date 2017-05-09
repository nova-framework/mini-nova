<?php

namespace Mini\Database\ORM\Relations;

use Mini\Database\ORM\Builder;
use Mini\Database\ORM\Model;
use Mini\Support\Collection;
use Mini\Support\Arr;

use Closure;


abstract class Relation
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
	public function __construct(Model $related, Model $parent)
	{
		$this->related = $related;
		$this->parent  = $parent;

		$this->query = $related->newQuery();

		//
		$this->addConstraints();
	}

	/**
	 * Set the base constraints on the relation query.
	 *
	 * @return void
	 */
	abstract public function addConstraints();

	/**
	 * Set the constraints for an eager load of the relation.
	 *
	 * @param  array  $models
	 * @return void
	 */
	abstract public function addEagerConstraints(array $models);

	/**
	 * Initialize the relation on a set of models.
	 *
	 * @param  array   $models
	 * @param  string  $relation
	 * @return array
	 */
	abstract public function initRelation(array $models, $relation);

	/**
	 * Match the eagerly loaded results to their parents.
	 *
	 * @param  array   $models
	 * @param  \Nova\Database\ORM\Collection  $results
	 * @param  string  $relation
	 * @return array
	 */
	abstract public function match(array $models, Collection $results, $relation);

	/**
	 * Get the result(s) of the relationship.
	 *
	 * @return mixed
	 */
	abstract public function getResults();

	/**
	 * Get the relationship for eager loading.
	 *
	 * @return \Mini\Database\ORM\Collection
	 */
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

	/**
	 * Touch all of the related models for the relationship.
	 *
	 * @return void
	 */
	public function touch()
	{
		$column = $this->related->getUpdatedAtColumn();

		$this->rawUpdate(array($column => $this->related->freshTimestampString()));
	}

	/**
	 * Run a raw update against the base query.
	 *
	 * @param  array  $attributes
	 * @return int
	 */
	public function rawUpdate(array $attributes = array())
	{
		return $this->query->update($attributes);
	}

	/**
	 * Get the underlying query for the relation.
	 *
	 * @return \Nova\Database\ORM\Builder
	 */
	public function getQuery()
	{
		return $this->query;
	}

   /**
	 * Get the parent model of the relation.
	 *
	 * @return \Mini\Database\ORM\Model
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * Get the related model of the relation.
	 *
	 * @return \Mini\Database\ORM\Model
	 */
	public function getRelated()
	{
		return $this->related;
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
		$result = call_user_func_array(array($this->query, $method), $parameters);

		if ($result === $this->query) return $this;

		return $result;
	}
}
