<?php

namespace Mini\Database\ORM;

use Mini\Database\ORM\Relations\Relation;
use Mini\Database\ORM\ModelNotFoundException;
use Mini\Database\ORM\Collection;
use Mini\Database\ORM\Model;
use Mini\Database\Query\Expression;
use Mini\Database\Query\Builder as QueryBuilder;
use Mini\Support\Arr;
use Mini\Support\Str;

use Closure;


class Builder
{
	/**
	 * The base Query Builder instance.
	 *
	 * @var \Mini\Database\Query\Builder
	 */
	protected $query;

	/**
	 * The model being queried.
	 *
	 * @var \Mini\Database\Model
	 */
	protected $model;

	/**
	 * The relationships that should be eager loaded.
	 *
	 * @var array
	 */
	protected $eagerLoad = array();


	/**
	 * Create a new Model Query Builder instance.
	 *
	 * @param  \Mini\Database\Query\Builder  $query
	 * @return void
	 */
	public function __construct(QueryBuilder $query)
	{
		$this->query = $query;
	}

	/**
	 * Find a model by its primary key.
	 *
	 * @param  mixed  $id
	 * @param  array  $columns
	 * @return mixed|static|null
	 */
	public function find($id, $columns = array('*'))
	{
		if (is_array($id)) {
			return $this->findMany($id, $columns);
		}

		$query = $this->query->where($this->model->getKeyName(), '=', $id);

		return $this->first($columns);
	}

	/**
	 * Find a model by its primary key.
	 *
	 * @param  array  $ids
	 * @param  array  $columns
	 * @return array|null|static
	 */
	public function findMany($ids, $columns = array('*'))
	{
		if (empty($ids)) return null;

		$query = $this->query->whereIn($this->model->getKeyName(), $ids);

		return $this->get($columns);
	}

	/**
	 * Find a model by its primary key or throw an exception.
	 *
	 * @param  mixed  $id
	 * @param  array  $columns
	 * @return \Mini\Database\ORM\Model|static
	 *
	 * @throws \Mini\Database\ModelNotFoundException
	 */
	public function findOrFail($id, $columns = array('*'))
	{
		if (! is_null($model = $this->find($id, $columns))) {
			return $model;
		}

		throw (new ModelNotFoundException)->setModel(get_class($this->model));
	}

	/**
	 * Execute the query and get the first result.
	 *
	 * @param  array  $columns
	 * @return \Mini\Database\ORM\Model|static|null
	 */
	public function first($columns = array('*'))
	{
		return $this->take(1)->get($columns)->first();
	}

	/**
	 * Execute the query and get the first result or throw an exception.
	 *
	 * @param  array  $columns
	 * @return \Mini\Database\Model|static
	 *
	 * @throws \Mini\Database\ModelNotFoundException
	 */
	public function firstOrFail($columns = array('*'))
	{
		if (! is_null($model = $this->first($columns))) {
			return $model;
		}

		throw (new ModelNotFoundException)->setModel(get_class($this->model));
	}

	/**
	 * Execute the query as a "select" statement.
	 *
	 * @param  array  $columns
	 * @return \Mini\Database\ORM\Collection|static[]
	 */
	public function get($columns = array('*'))
	{
		$models = $this->getModels($columns);

		if (count($models) > 0) {
			$models = $this->eagerLoadRelations($models);
		}

		return $this->model->newCollection($models);
	}

	/**
	 * Pluck a single column from the database.
	 *
	 * @param  string  $column
	 * @return mixed
	 */
	public function pluck($column)
	{
		$result = $this->first(array($column));

		if ($result) return $result->{$column};
	}

	/**
	 * Chunk the results of the query.
	 *
	 * @param  int  $count
	 * @param  callable  $callback
	 * @return void
	 */
	public function chunk($count, callable $callback)
	{
		$results = $this->forPage($page = 1, $count)->get();

		while (count($results) > 0) {
			call_user_func($callback, $results);

			$page++;

			$results = $this->forPage($page, $count)->get();
		}
	}

	/**
	 * Get an array with the values of a given column.
	 *
	 * @param  string  $column
	 * @param  string  $key
	 * @return array
	 */
	public function lists($column, $key = null)
	{
		$results = $this->query->lists($column, $key);

		if ($this->model->hasGetMutator($column)) {
			foreach ($results as $key => &$value) {
				$fill = array($column => $value);

				$value = $this->model->newFromBuilder($fill)->$column;
			}
		}

		return $results;
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
		// Get the Pagination Factory instance.
		$paginator = $this->query->getConnection()->getPaginator();

		$perPage = $perPage ?: $this->model->getPerPage();

		if (isset($this->query->groups)) {
			return $this->groupedPaginate($paginator, $perPage, $columns);
		} else {
			return $this->ungroupedPaginate($paginator, $perPage, $columns);
		}
	}

	/**
	 * Get a paginator for a grouped statement.
	 *
	 * @param  \Mini\Pagination\Environment  $paginator
	 * @param  int	$perPage
	 * @param  array  $columns
	 * @return \Mini\Pagination\Paginator
	 */
	protected function groupedPaginate($paginator, $perPage, $columns)
	{
		$results = $this->get($columns);

		return $this->query->buildRawPaginator($paginator, $results, $perPage);
	}

	/**
	 * Get a paginator for an ungrouped statement.
	 *
	 * @param  \Mini\Pagination\Environment  $paginator
	 * @param  int	$perPage
	 * @param  array  $columns
	 * @return \Mini\Pagination\Paginator
	 */
	protected function ungroupedPaginate($paginator, $perPage, $columns)
	{
		$total = $this->query->getPaginationCount();

		$page = $paginator->getCurrentPage($total);

		$query = $this->query->forPage($page, $perPage);

		// Retrieve the results from database.
		$results = $this->get($columns)->all();

		return $paginator->make($results, $total, $perPage);
	}

	/**
	 * Get a Paginator only supporting simple next and previous links.
	 *
	 * This is more efficient on larger data-sets, etc.
	 *
	 * @param  int	$perPage
	 * @param  array  $columns
	 * @return \Mini\Pagination\Paginator
	 */
	public function simplePaginate($perPage = null, $columns = array('*'))
	{
		// Get the Pagination Factory instance.
		$paginator = $this->connection->getPaginator();

		$perPage = $perPage ?: $this->model->getPerPage();

		$page = $paginator->getCurrentPage();

		$query = $this->skip(($page - 1) * $perPage)->take($perPage + 1);

		// Retrieve the results from database.
		$results = $this->get($columns)->all();

		return $paginator->make($results, $perPage);
	}

	/**
	 * Get the hydrated models.
	 *
	 * @param  array  $columns
	 * @return \Mini\Database\Entity[]
	 */
	public function getModels($columns = array('*'))
	{
		$results = $this->query->get($columns);

		$connection = $this->model->getConnectionName();

		//
		$models = array();

		foreach ($results as $result) {
			$models[] = $model = $this->model->newFromBuilder($result);

			$model->setConnection($connection);
		}

		return $models;
	}

	/**
	 * Update a record in the database.
	 *
	 * @param  array  $values
	 * @return int
	 */
	public function update(array $values)
	{
		return $this->query->update($this->addUpdatedAtColumn($values));
	}

	/**
	 * Increment a column's value by a given amount.
	 *
	 * @param  string  $column
	 * @param  int	 $amount
	 * @param  array   $extra
	 * @return int
	 */
	public function increment($column, $amount = 1, array $extra = array())
	{
		$extra = $this->addUpdatedAtColumn($extra);

		return $this->query->increment($column, $amount, $extra);
	}

	/**
	 * Decrement a column's value by a given amount.
	 *
	 * @param  string  $column
	 * @param  int	 $amount
	 * @param  array   $extra
	 * @return int
	 */
	public function decrement($column, $amount = 1, array $extra = array())
	{
		$extra = $this->addUpdatedAtColumn($extra);

		return $this->query->decrement($column, $amount, $extra);
	}

	/**
	 * Add the "updated at" column to an array of values.
	 *
	 * @param  array  $values
	 * @return array
	 */
	protected function addUpdatedAtColumn(array $values)
	{
		if (! $this->model->usesTimestamps()) return $values;

		$column = $this->model->getUpdatedAtColumn();

		return Arr::add($values, $column, $this->model->freshTimestampString());
	}

	/**
	 * Eager load the relationships for the models.
	 *
	 * @param  array  $models
	 * @return array
	 */
	public function eagerLoadRelations(array $models)
	{
		foreach ($this->eagerLoad as $name => $constraints) {
			if (strpos($name, '.') === false) {
				$models = $this->loadRelation($models, $name, $constraints);
			}
		}

		return $models;
	}

	/**
	 * Eagerly load the relationship on a set of models.
	 *
	 * @param  array	 $models
	 * @param  string	$name
	 * @param  \Closure  $constraints
	 * @return array
	 */
	protected function loadRelation(array $models, $name, Closure $constraints)
	{
		$relation = $this->getRelation($name);

		$query = $relation->addEagerConstraints($models);

		call_user_func($constraints, $relation);

		//
		$models = $relation->initRelation($models, $name);

		$results = $relation->getEager();

		return $relation->match($models, $results, $name);
	}

	/**
	 * Get the relation instance for the given relation name.
	 *
	 * @param  string  $relation
	 * @return \Mini\Database\ORM\Relation
	 */
	public function getRelation($relation)
	{
		$relation = Relation::noConstraints(function() use ($relation)
		{
			return $this->getModel()->$relation();
		});

		$nested = $this->nestedRelations($relation);

		if (count($nested) > 0) {
			$query->getQuery()->with($nested);
		}

		return $relation;
	}

	/**
	 * Get the deeply nested relations for a given top-level relation.
	 *
	 * @param  string  $relation
	 * @return array
	 */
	protected function nestedRelations($relation)
	{
		$nested = array();

		foreach ($this->eagerLoad as $name => $constraints) {
			if ($this->isNested($name, $relation)) {
				$key = substr($name, strlen($relation .'.'));

				$nested[$key] = $constraints;
			}
		}

		return $nested;
	}

	/**
	 * Determine if the relationship is nested.
	 *
	 * @param  string  $name
	 * @param  string  $relation
	 * @return bool
	 */
	protected function isNested($name, $relation)
	{
		return Str::contains($name, '.') && Str::startsWith($name, $relation .'.');
	}

	/**
	 * Add a basic where clause to the query.
	 *
	 * @param  string  $column
	 * @param  string  $operator
	 * @param  mixed   $value
	 * @param  string  $boolean
	 * @return $this
	 */
	public function where($column, $operator = null, $value = null, $boolean = 'and')
	{
		if ($column instanceof Closure) {
			$query = $this->model->newQuery();

			call_user_func($column, $query);

			$this->query->addNestedWhereQuery($query->getQuery(), $boolean);
		} else {
			call_user_func_array(array($this->query, 'where'), func_get_args());
		}

		return $this;
	}

	/**
	 * Add an "or where" clause to the query.
	 *
	 * @param  string  $column
	 * @param  string  $operator
	 * @param  mixed   $value
	 * @return \Mini\Database\ORM\Builder|static
	 */
	public function orWhere($column, $operator = null, $value = null)
	{
		return $this->where($column, $operator, $value, 'or');
	}

	/**
	 * Add a relationship count condition to the query.
	 *
	 * @param  string  $relation
	 * @param  string  $operator
	 * @param  int	 $count
	 * @param  string  $boolean
	 * @param  \Closure|null  $callback
	 * @return \Mini\Database\ORM\Builder|static
	 */
	public function has($relation, $operator = '>=', $count = 1, $boolean = 'and', Closure $callback = null)
	{
		if (strpos($relation, '.') !== false) {
			return $this->hasNested($relation, $operator, $count, $boolean, $callback);
		}

		$relation = $this->getHasRelationQuery($relation);

		$query = $relation->getRelationCountQuery($relation->getRelated()->newQuery(), $this);

		if ($callback) call_user_func($callback, $query);

		return $this->addHasWhere($query, $relation, $operator, $count, $boolean);
	}

	/**
	 * Add nested relationship count conditions to the query.
	 *
	 * @param  string  $relations
	 * @param  string  $operator
	 * @param  int	 $count
	 * @param  string  $boolean
	 * @param  \Closure  $callback
	 * @return \Mini\Database\ORM\Builder|static
	 */
	protected function hasNested($relations, $operator = '>=', $count = 1, $boolean = 'and', $callback = null)
	{
		$relations = explode('.', $relations);

		$closure = function ($query) use (&$closure, &$relations, $operator, $count, $boolean, $callback)
		{
			if (count($relations) > 1) {
				$query->whereHas(array_shift($relations), $closure);
			} else {
				$query->has(array_shift($relations), $operator, $count, $boolean, $callback);
			}
		};

		return $this->whereHas(array_shift($relations), $closure);
	}

	/**
	 * Add a relationship count condition to the query.
	 *
	 * @param  string  $relation
	 * @param  string  $boolean
	 * @param  \Closure|null  $callback
	 * @return \Mini\Database\ORM\Builder|static
	 */
	public function doesntHave($relation, $boolean = 'and', Closure $callback = null)
	{
		return $this->has($relation, '<', 1, $boolean, $callback);
	}

	/**
	 * Add a relationship count condition to the query with where clauses.
	 *
	 * @param  string	$relation
	 * @param  \Closure  $callback
	 * @param  string	$operator
	 * @param  int	   $count
	 * @return \Mini\Database\ORM\Builder|static
	 */
	public function whereHas($relation, Closure $callback, $operator = '>=', $count = 1)
	{
		return $this->has($relation, $operator, $count, 'and', $callback);
	}

	/**
	 * Add a relationship count condition to the query with where clauses.
	 *
	 * @param  string  $relation
	 * @param  \Closure|null  $callback
	 * @return \Mini\Database\ORM\Builder|static
	 */
	public function whereDoesntHave($relation, Closure $callback = null)
	{
		return $this->doesntHave($relation, 'and', $callback);
	}

	/**
	 * Add a relationship count condition to the query with an "or".
	 *
	 * @param  string  $relation
	 * @param  string  $operator
	 * @param  int	 $count
	 * @return \Mini\Database\ORM\Builder|static
	 */
	public function orHas($relation, $operator = '>=', $count = 1)
	{
		return $this->has($relation, $operator, $count, 'or');
	}

	/**
	 * Add a relationship count condition to the query with where clauses and an "or".
	 *
	 * @param  string	$relation
	 * @param  \Closure  $callback
	 * @param  string	$operator
	 * @param  int	   $count
	 * @return \Mini\Database\ORM\Builder|static
	 */
	public function orWhereHas($relation, Closure $callback, $operator = '>=', $count = 1)
	{
		return $this->has($relation, $operator, $count, 'or', $callback);
	}

	/**
	 * Add the "has" condition where clause to the query.
	 *
	 * @param  \Mini\Database\ORM\Builder  $hasQuery
	 * @param  \Mini\Database\ORM\Relations\Relation  $relation
	 * @param  string  $operator
	 * @param  int  $count
	 * @param  string  $boolean
	 * @return \Mini\Database\ORM\Builder
	 */
	protected function addHasWhere(Builder $hasQuery, Relation $relation, $operator, $count, $boolean)
	{
		$this->mergeWheresToHas($hasQuery, $relation);

		if (is_numeric($count)) {
			$count = new Expression($count);
		}

		return $this->where(new Expression('(' .$hasQuery->toSql() .')'), $operator, $count, $boolean);
	}

	/**
	 * Merge the "wheres" from a relation query to a has query.
	 *
	 * @param  \Mini\Database\ORM\Builder  $hasQuery
	 * @param  \Mini\Database\ORM\Relations\Relation  $relation
	 * @return void
	 */
	protected function mergeWheresToHas(Builder $hasQuery, Relation $relation)
	{
		$relationQuery = $relation->getBaseQuery();

		$hasQuery = $hasQuery->getModel()->removeGlobalScopes($hasQuery);

		$hasQuery->mergeWheres(
			$relationQuery->wheres, $relationQuery->getBindings()
		);

		$this->query->mergeBindings($hasQuery->getQuery());
	}

	/**
	 * Get the "has relation" base query instance.
	 *
	 * @param  string  $relation
	 * @return \Mini\Database\ORM\Builder
	 */
	protected function getHasRelationQuery($relation)
	{
		return Relation::noConstraints(function() use ($relation)
		{
			return $this->getModel()->$relation();
		});
	}

	/**
	 * Set the relationships that should be eager loaded.
	 *
	 * @param  mixed  $relations
	 * @return $this
	 */
	public function with($relations)
	{
		if (is_string($relations)) {
			$relations = func_get_args();
		}

		$eagers = $this->parseRelations($relations);

		$this->eagerLoad = array_merge($this->eagerLoad, $eagers);

		return $this;
	}

	/**
	 * Prevent the specified relations from being eager loaded.
	 *
	 * @param  mixed  $relations
	 * @return $this
	 */
	public function without($relations)
	{
		if (is_string($relations)) $relations = func_get_args();

		$this->eagerLoad = array_diff_key($this->eagerLoad, array_flip($relations));

		return $this;
	}

	/**
	 * Add subselect queries to count the relations.
	 *
	 * @param  mixed  $relations
	 * @return $this
	 */
	public function withCount($relations)
	{
		if (is_string($relations)) $relations = func_get_args();

		// If no columns are set, add the default * columns.
		if (is_null($this->query->columns)) {
			$this->query->select($this->query->from .'.*');
		}

		$relations = $this->parseRelations($relations);

		foreach ($relations as $name => $constraints) {
			$relation = $this->getHasRelationQuery($name);

			$query = $relation->getRelationCountQuery($relation->getRelated()->newQuery(), $this);

			call_user_func($constraints, $query);

			$this->mergeWheresToHas($query, $relation);

			$this->selectSub($query->getQuery(), Str::snake($name) .'_count');
		}

		return $this;
	}

	/**
	 * Parse a list of relations into individuals.
	 *
	 * @param  array  $relations
	 * @return array
	 */
	protected function parseRelations(array $relations)
	{
		$results = array();

		foreach ($relations as $name => $constraints) {
			if (is_numeric($name)) {
				list($name, $constraints) = array($constraints, function () {});
			}

			$results = $this->parseNested($name, $results);

			$results[$name] = $constraints;
		}

		return $results;
	}

	/**
	 * Parse the nested relationships in a relation.
	 *
	 * @param  string  $name
	 * @param  array   $results
	 * @return array
	 */
	protected function parseNested($name, $results)
	{
		$progress = array();

		foreach (explode('.', $name) as $segment) {
			$progress[] = $segment;

			if (! isset($results[$last = implode('.', $progress)])) {
				$results[$last] = function () {};
			}
		}

		return $results;
	}

	/**
	 * Call the given model scope on the underlying model.
	 *
	 * @param  string  $scope
	 * @param  array   $parameters
	 * @return \Mini\Database\Query\Builder
	 */
	protected function callScope($scope, $parameters)
	{
		array_unshift($parameters, $this);

		return call_user_func_array(array($this->model, $scope), $parameters) ?: $this;
	}

	/**
	 * Get the underlying query builder instance.
	 *
	 * @return \Mini\Database\Query\Builder|static
	 */
	public function getQuery()
	{
		return $this->query;
	}

	/**
	 * Set the underlying query builder instance.
	 *
	 * @param  \Mini\Database\Query\Builder  $query
	 * @return void
	 */
	public function setQuery($query)
	{
		$this->query = $query;
	}

	/**
	 * Get the relationships being eagerly loaded.
	 *
	 * @return array
	 */
	public function getEagerLoads()
	{
		return $this->eagerLoad;
	}

	/**
	 * Set the relationships being eagerly loaded.
	 *
	 * @param  array  $eagerLoad
	 * @return void
	 */
	public function setEagerLoads(array $eagerLoad)
	{
		$this->eagerLoad = $eagerLoad;
	}

	/**
	 * Get the model instance being queried.
	 *
	 * @return \Mini\Database\ORM\Model
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * Set a model instance for the model being queried.
	 *
	 * @param  \Mini\Database\ORM\Model  $model
	 * @return \Mini\Database\ORM\Builder
	 */
	public function setModel(Model $model)
	{
		$this->model = $model;

		$this->query->from($model->getTable());

		return $this;
	}

	/**
	 * Dynamically handle calls into the query instance.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		if (method_exists($this->model, $scope = 'scope' .ucfirst($method))) {
			return $this->callScope($scope, $parameters);
		}

		$result = call_user_func_array(array($this->query, $method), $parameters);

		if ($result === $this->query) {
			return $this;
		}

		return $result;
	}

	/**
	 * Force a clone of the underlying query builder when cloning.
	 *
	 * @return void
	 */
	public function __clone()
	{
		$this->query = clone $this->query;
	}

}
