<?php

namespace Mini\Database\ORM\Relations;

use Mini\Database\ORM\Relations\Relation;
use Mini\Database\ORM\Model;
use Mini\Support\Collection;


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
	 * Set the constraints for an eager load of the relation.
	 *
	 * @param  array  $models
	 * @return void
	 */
	public function addEagerConstraints(array $models)
	{
		$keys = array();

		foreach ($models as $model) {
			if (! is_null($value = $model->getAttribute($this->foreignKey))) {
				$keys[] = $value;
			}
		}

		if (count($keys) == 0) {
			$keys = array(0);
		} else {
			$keys = array_values(array_unique($keys));
		}

		//
		$key = $this->related->getTable() .'.' .$this->otherKey;

		$this->query->whereIn($key, $keys);
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
	 * @param  \Nova\Database\ORM\Collection  $results
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

}
