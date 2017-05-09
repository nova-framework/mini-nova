<?php

namespace Mini\Database\ORM\Relations;

use Mini\Database\ORM\Relations\Relation;
use Mini\Database\ORM\Builder;
use Mini\Database\ORM\Collection;
use Mini\Database\ORM\Model;
use Mini\Database\Query\Expression;


class BelongsTo extends Relation
{
	/**
	 * The foreign key of the parent model.
	 *
	 * @var string
	 */
	protected $foreignKey;

	/**
	 * The associated key on the parent model.
	 *
	 * @var string
	 */
	protected $otherKey;

	/**
	 * The name of the relationship.
	 *
	 * @var string
	 */
	protected $relation;


	/**
	 * Create a new belongs to relationship instance.
	 *
	 * @param  \Mini\Database\ORM\Builder  $query
	 * @param  \Mini\Database\ORM\Model  $parent
	 * @param  string  $foreignKey
	 * @param  string  $otherKey
	 * @param  string  $relation
	 * @return void
	 */
	public function __construct(Model $related, Model $parent, $foreignKey, $otherKey, $relation)
	{
		$this->foreignKey = $foreignKey;
		$this->otherKey   = $otherKey;
		$this->relation   = $relation;

		//
		parent::__construct($related, $parent);
	}

	/**
	 * Get the result(s) of the relationship.
	 *
	 * @return mixed
	 */
	public function getResults()
	{
		return $this->query->first();
	}

	/**
	 * Set the base constraints on the relation query.
	 *
	 * @return void
	 */
	public function addConstraints()
	{
		if (static::$constraints) {
			$key = $this->related->getTable() .'.' .$this->otherKey;

			$value = $this->parent->getAttribute($this->foreignKey);

			$this->query->where($key, '=', $value);
		}
	}

	/**
	 * Add the constraints for a relationship count query.
	 *
	 * @param  \Mini\Database\ORM\Builder  $query
	 * @param  \Mini\Database\ORM\Builder  $parent
	 * @return \Mini\Database\ORM\Builder
	 */
	public function getRelationCountQuery(Builder $query, Builder $parent)
	{
		$query->select(new Expression('count(*)'));

		$otherKey = $this->wrap($query->getModel()->getTable() .'.' .$this->otherKey);

		return $query->where($this->getQualifiedForeignKey(), '=', new Expression($otherKey));
	}

	/**
	 * Set the constraints for an eager load of the relation.
	 *
	 * @param  array  $models
	 * @return void
	 */
	public function addEagerConstraints(array $models)
	{
		$key = $this->related->getTable() .'.' .$this->otherKey;

		$this->query->whereIn($key, $this->getEagerModelKeys($models));
	}

	/**
	 * Gather the keys from an array of related models.
	 *
	 * @param  array  $models
	 * @return array
	 */
	protected function getEagerModelKeys(array $models)
	{
		$keys = array();

		foreach ($models as $model) {
			if (! is_null($value = $model->getAttribute($this->foreignKey))) {
				$keys[] = $value;
			}
		}

		if (count($keys) == 0) {
			return array(0);
		}

		return array_values(array_unique($keys));
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
			$model->setRelation($relation, null);
		}

		return $models;
	}

	/**
	 * Match the eagerly loaded results to their parents.
	 *
	 * @param  array   $models
	 * @param  \Mini\Database\ORM\Collection  $results
	 * @param  string  $relation
	 * @return array
	 */
	public function match(array $models, Collection $results, $relation)
	{
		$dictionary = array();

		foreach ($results as $result) {
			$key = $result->getAttribute($this->otherKey);

			$dictionary[$key] = $result;
		}

		foreach ($models as $model) {
			$key = $model->getAttribute($this->foreignKey);

			if (isset($dictionary[$key])) {
				$value = $dictionary[$key];

				$model->setRelation($relation, $value);
			}
		}

		return $models;
	}

	/**
	 * Associate the model instance to the given parent.
	 *
	 * @param  \Mini\Database\ORM\Model  $model
	 * @return \Mini\Database\ORM\Model
	 */
	public function associate(Model $model)
	{
		$this->parent->setAttribute($this->foreignKey, $model->getAttribute($this->otherKey));

		return $this->parent->setRelation($this->relation, $model);
	}

	/**
	 * Dissociate previously associated model from the given parent.
	 *
	 * @return \Mini\Database\ORM\Model
	 */
	public function dissociate()
	{
		$this->parent->setAttribute($this->foreignKey, null);

		return $this->parent->setRelation($this->relation, null);
	}

	/**
	 * Get the foreign key of the relationship.
	 *
	 * @return string
	 */
	public function getForeignKey()
	{
		return $this->foreignKey;
	}

	/**
	 * Get the fully qualified foreign key of the relationship.
	 *
	 * @return string
	 */
	public function getQualifiedForeignKey()
	{
		return $this->parent->getTable() .'.' .$this->foreignKey;
	}

	/**
	 * Get the associated key of the relationship.
	 *
	 * @return string
	 */
	public function getOtherKey()
	{
		return $this->otherKey;
	}
	
	/**
	 * Get the fully qualified associated key of the relationship.
	 *
	 * @return string
	 */
	public function getQualifiedOtherKeyName()
	{
		return $this->related->getTable() .'.' .$this->otherKey;
	}
}
