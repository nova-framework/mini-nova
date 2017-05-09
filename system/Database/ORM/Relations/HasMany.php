<?php

namespace Mini\Database\ORM\Relations;

use Mini\Database\ORM\Relations\Relation;
use Mini\Database\ORM\Model;
use Mini\Support\Collection;


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
     * @param  \Nova\Database\ORM\Builder  $query
     * @param  \Nova\Database\ORM\Model  $parent
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
		$value = $this->related->newCollection();

		foreach ($models as $model) {
			$model->setRelation($relation, $value);
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
			$key = $result->getAttribute($this->foreignKey);

			$dictionary[$key][] = $result;
		}

		foreach ($models as $model) {
			$key = $model->getAttribute($this->localKey);

			if (isset($dictionary[$key])) {
				$value = $dictionary[$key];

				$model->setRelation($relation, $this->getRelated()->newCollection($value));
			}
		}

		return $models;
	}
}
