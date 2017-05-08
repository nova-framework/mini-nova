<?php

namespace Mini\Database;

use Mini\Database\Query\Expression;
use Mini\Database\Query\Builder as QueryBuilder;
use Mini\Database\Model;
use Mini\Database\ModelNotFoundException;
use Mini\Support\Arr;
use Mini\Support\Collection;

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
     * @param  int    $perPage
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
     * @param  int    $perPage
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
     * @param  int    $perPage
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
     * @param  int    $perPage
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

        //
        $models = array();

        foreach ($results as $result) {
            $models[] = $this->model->newFromBuilder($result);
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
     * @param  int     $amount
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
     * @param  int     $amount
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
        $result = call_user_func_array(array($this->query, $method), $parameters);

        if ($result === $this->query) return $this;

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
