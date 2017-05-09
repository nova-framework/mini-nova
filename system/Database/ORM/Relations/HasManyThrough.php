<?php

namespace Mini\Database\ORM\Relations;

use Mini\Database\ORM\Builder;
use Mini\Database\ORM\Model;
use Mini\Database\ORM\Collection;
use Mini\Database\ORM\ModelNotFoundException;
use Mini\Database\Query\Expression;


class HasManyThrough extends Relation
{
	/**
	 * The distance parent model instance.
	 *
	 * @var \Mini\Database\ORM\Model
	 */
	protected $farParent;

	/**
	 * The near key on the relationship.
	 *
	 * @var string
	 */
	protected $firstKey;

	/**
	 * The far key on the relationship.
	 *
	 * @var string
	 */
	protected $secondKey;


	/**
	 * Create a new has many relationship instance.
	 *
	 * @param  \Mini\Database\ORM\Model  $related
	 * @param  \Mini\Database\ORM\Model  $farParent
	 * @param  \Mini\Database\ORM\Model  $parent
	 * @param  string  $firstKey
	 * @param  string  $secondKey
	 * @return void
	 */
	public function __construct(Model $related, Model $farParent, Model $parent, $firstKey, $secondKey)
	{
		$this->firstKey = $firstKey;
		$this->secondKey = $secondKey;
		$this->farParent = $farParent;

		parent::__construct($related, $parent);
	}

	/**
	 * Get the results of the relationship.
	 *
	 * @return mixed
	 */
	public function getResults()
	{
		return $this->get();
	}

	/**
	 * Set the base constraints on the relation query.
	 *
	 * @return void
	 */
	public function addConstraints()
	{
		$this->setJoin();

		if (static::$constraints) {
			$this->query->where( $this->parent->getTable() .'.' .$this->firstKey, '=', $this->farParent->getKey());
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
		$this->setJoin($query);

		$query->select(new Expression('count(*)'));

		//
		$key = $this->wrap($this->parent->getTable() .'.' .$this->firstKey);

		return $query->where($this->getHasCompareKey(), '=', new Expression($key));
	}

	/**
	 * Set the join clause on the query.
	 *
	 * @param  \Mini\Database\ORM\Builder|null  $query
	 * @return void
	 */
	protected function setJoin(Builder $query = null)
	{
		$query = $query ?: $this->query;

		$foreignKey = $this->related->getTable() .'.' .$this->secondKey;

		$query->join($this->parent->getTable(), $this->getQualifiedParentKeyName(), '=', $foreignKey);
	}

	/**
	 * Set the constraints for an eager load of the relation.
	 *
	 * @param  array  $models
	 * @return void
	 */
	public function addEagerConstraints(array $models)
	{
		$table = $this->parent->getTable();

		$this->query->whereIn($table.'.'.$this->firstKey, $this->getKeys($models));
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
		$dictionary = array();

		foreach ($results as $result) {
			$key = $result->getAttribute($this->firstKey);

			$dictionary[$key][] = $result;
		}

		foreach ($models as $model) {
			$key = $model->getKey();

			if (isset($dictionary[$key])) {
				$value = $dictionary[$key];

				$model->setRelation($relation, $this->related->newCollection($value));
			}
		}

		return $models;
	}

	/**
	 * Execute the query and get the first result.
	 *
	 * @param  array  $columns
	 * @return \Mini\Database\ORM\Model|static|null
	 */
	public function first($columns = array('*'))
	{
		$results = $this->take(1)->get($columns);

		return (count($results) > 0) ? $results->first() : null;
	}

	/**
	 * Execute the query and get the first result or throw an exception.
	 *
	 * @param  array  $columns
	 * @return \Mini\Database\ORM\Model|static
	 *
	 * @throws \Mini\Database\ORM\ModelNotFoundException
	 */
	public function firstOrFail($columns = array('*'))
	{
		if (! is_null($model = $this->first($columns))) return $model;

		throw new ModelNotFoundException;
	}

	/**
	 * Execute the query as a "select" statement.
	 *
	 * @param  array  $columns
	 * @return \Mini\Database\ORM\Collection
	 */
	public function get($columns = array('*'))
	{
		$select = $this->getSelectColumns($columns);

		$models = $this->query->addSelect($select)->getModels();

		if (count($models) > 0) {
			$models = $this->query->eagerLoadRelations($models);
		}

		return $this->related->newCollection($models);
	}

	/**
	 * Set the select clause for the relation query.
	 *
	 * @param  array  $columns
	 * @return \Mini\Database\ORM\Relations\BelongsToMany
	 */
	protected function getSelectColumns(array $columns = array('*'))
	{
		if ($columns == array('*')) {
			$columns = array($this->related->getTable() .'.*');
		}

		return array_merge($columns, array($this->parent->getTable() .'.' .$this->firstKey));
	}

	/**
	 * Get a paginator for the "select" statement.
	 *
	 * @param  int	$perPage
	 * @param  array  $columns
	 * @return \Mini\Pagination\Paginator
	 */
	public function paginate($perPage = null, $columns = array('*'))
	{
		$this->query->addSelect($this->getSelectColumns($columns));

		$pager = $this->query->paginate($perPage, $columns);

		return $pager;
	}

	/**
	 * Get the key for comparing against the parent key in "has" query.
	 *
	 * @return string
	 */
	public function getHasCompareKey()
	{
		return $this->farParent->getQualifiedKeyName();
	}

}
