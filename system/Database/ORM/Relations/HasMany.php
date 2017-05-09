<?php

namespace Mini\Database\ORM\Relations;

use Mini\Database\ORM\Relations\Relation;
use Mini\Database\ORM\Collection;
use Mini\Database\ORM\Model;


class HasMany extends Relation
{
	/**
	 * The foreign key of the parent model.
	 *
	 * @var string
	 */
	protected $foreignKey;

	/**
	 * The local key of the parent model.
	 *
	 * @var string
	 */
	protected $localKey;

	/**
	 * Create a new has many relationship instance.
	 *
	 * @param  \Mini\Database\ORM\Builder  $query
	 * @param  \Mini\Database\ORM\Model  $parent
	 * @param  string  $foreignKey
	 * @param  string  $localKey
	 * @return void
	 */
	public function __construct(Model $related, Model $parent, $foreignKey, $localKey)
	{
		$this->localKey   = $localKey;
		$this->foreignKey = $foreignKey;

		parent::__construct($related, $parent);
	}

	/**
	 * Get the result(s) of the relationship.
	 *
	 * @return mixed
	 */
	public function getResults()
	{
		return $this->query->get();
	}

	/**
	 * Set the base constraints on the relation query.
	 *
	 * @return void
	 */
	public function addConstraints()
	{
		if (static::$constraints) {
			$value = $this->parent->getAttribute($this->localKey);

			$this->query->where($this->foreignKey, '=', $value);
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
		$keys = $this->getKeys($models, $this->localKey);

		$this->query->whereIn($this->foreignKey, $keys);
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
			$model->setRelation($relation, $this->related->newCollection());
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
		$dictionary = $this->buildDictionary($results);

		foreach ($models as $model) {
			$key = $model->getAttribute($this->localKey);

			if (isset($dictionary[$key])) {
				$value = $dictionary[$key];

				$model->setRelation($relation, $this->related->newCollection($value));
			}
		}

		return $models;
	}

	/**
	 * Build model dictionary keyed by the relation's foreign key.
	 *
	 * @param  \Mini\Database\ORM\Collection  $results
	 * @return array
	 */
	protected function buildDictionary(Collection $results)
	{
		$dictionary = array();

		$foreign = $this->getPlainForeignKey();

		foreach ($results as $result) {
			$key = $result->getAttribute($foreign);

			$dictionary[$key][] = $result;
		}

		return $dictionary;
	}

	/**
	 * Attach a model instance to the parent model.
	 *
	 * @param  \Mini\Database\ORM\Model  $model
	 * @return \Mini\Database\ORM\Model
	 */
	public function save(Model $model)
	{
		$model->setAttribute($this->getPlainForeignKey(), $this->getParentKey());

		return $model->save() ? $model : false;
	}

	/**
	 * Get the key for comparing against the parent key in "has" query.
	 *
	 * @return string
	 */
	public function getHasCompareKey()
	{
		return $this->getForeignKey();
	}

	/**
	 * Get the plain foreign key.
	 *
	 * @return string
	 */
	public function getPlainForeignKey()
	{
		$segments = explode('.', $this->foreignKey);

		return end($segments);
	}
}
