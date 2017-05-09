<?php

namespace Mini\Database\ORM\Relations;

use Mini\Database\ORM\Relations\HasMany;
use Mini\Database\ORM\Collection;
use Mini\Database\ORM\Model;


class HasOne extends Relation
{
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
		$dictionary = $this->buildDictionary($results);

		foreach ($models as $model) {
			$key = $model->getAttribute($this->localKey);

			if (isset($dictionary[$key])) {
				$value = $dictionary[$key];

				$model->setRelation($relation, reset($value));
			}
		}

		return $models;
	}
}

