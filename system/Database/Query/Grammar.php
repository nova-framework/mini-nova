<?php
/**
 * Query - A simple Database QueryBuilder.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */

namespace Mini\Database\Query;

use Mini\Database\Query\Builder;
use Mini\Database\Query\Expression;


class Grammar
{
    /**
     * The grammar table prefix.
     *
     * @var string
     */
    protected $tablePrefix = '';

    protected $selectComponents = array(
        'aggregate',
        'columns',
        'from',
        'joins',
        'wheres',
        'groups',
        'havings',
        'orders',
        'limit',
        'unions',
        'offset'
    );


    /**
     * Compile a select query into SQL.
     *
     * @param  \Database\Query\Builder  $query
     * @return string
     */
    public function compileSelect(Builder $query)
    {
        if (is_null($query->columns)) {
            $query->columns = array('*');
        }

        return trim($this->concatenate($this->compileComponents($query)));
    }

    /**
     * Compile the components necessary for a select clause.
     *
     * @param  \Database\Query\Builder  $query
     * @return array
     */
    protected function compileComponents(Builder $query)
    {
        $sql = array();

        foreach ($this->selectComponents as $component) {
            if (! is_null($query->{$component})) {
                $method = 'compile' .ucfirst($component);

                $sql[$component] = call_user_func(array($this, $method), $query, $query->{$component});
            }
        }

        return $sql;
    }

    /**
     * Compile an aggregated select clause.
     *
     * @param  \Database\Query\Builder  $query
     * @param  array  $aggregate
     * @return string
     */
    protected function compileAggregate(Builder $query, $aggregate)
    {
        $column = $this->columnize($aggregate['columns']);

        if ($query->distinct && ($column !== '*')) {
            $column = 'DISTINCT ' .$column;
        }

        return 'SELECT ' .$aggregate['function'] .'(' .$column .') AS aggregate';
    }

    /**
     * Compile the "SELECT *" portion of the query.
     *
     * @param  \Database\Query\Builder  $query
     * @param  array  $columns
     * @return string
     */
    protected function compileColumns(Builder $query, $columns)
    {
        if (is_null($query->aggregate)) {
            $select = $query->distinct ? 'SELECT DISTINCT ' : 'SELECT ';

            return $select .$this->columnize($columns);
        }

        return '';
    }

    /**
     * Compile the "FROM" portion of the query.
     *
     * @param  \Database\Query\Builder  $query
     * @param  string  $table
     * @return string
     */
    protected function compileFrom(Builder $query, $table)
    {
        return 'FROM ' .$this->wrapTable($table);
    }

    /**
     * Compile the "JOIN" portions of the query.
     *
     * @param  \Database\Query\Builder  $query
     * @param  array  $joins
     * @return string
     */
    protected function compileJoins(Builder $query, $joins)
    {
        $sql = array();

        foreach ($joins as $join) {
            $table = $this->wrapTable($join->table);

            $clauses = array();

            foreach ($join->clauses as $clause) {
                $clauses[] = $this->compileJoinConstraint($clause);
            }

            $clauses[0] = $this->removeLeadingBoolean($clauses[0]);

            $clauses = implode(' ', $clauses);

            $type = $join->type;

            $sql[] = "$type JOIN $table ON $clauses";
        }

        return implode(' ', $sql);
    }

    /**
     * Create a join clause constraint segment.
     *
     * @param  array   $clause
     * @return string
     */
    protected function compileJoinConstraint(array $clause)
    {
        $first = $this->wrap($clause['first']);

        $second = $clause['where'] ? '?' : $this->wrap($clause['second']);

        $boolean = strtoupper($clause['boolean']);

        return "$boolean $first {$clause['operator']} $second";
    }

    /**
     * Compile the "WHERE" portions of the query.
     *
     * @param  \Database\Query\Builder  $query
     * @return string
     */
    protected function compileWheres(Builder $query)
    {
        $sql = array();

        if (is_null($query->wheres)) {
            return '';
        }

        foreach ($query->wheres as $where) {
            $method = "where{$where['type']}";

            $sql[] = strtoupper($where['boolean']) .' ' .call_user_func(array($this, $method), $where);
        }

        if (count($sql) > 0) {
            $sql = implode(' ', $sql);

            return 'WHERE ' .preg_replace('/AND |OR /', '', $sql, 1);
        }

        return '';
    }

    /**
     * Compile a nested where clause.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereNested($where)
    {
        $nested = $where['query'];

        return '(' .substr($this->compileWheres($nested), 6) .')';
    }

    /**
     * Compile a where condition with a sub-select.
     *
     * @param  array   $where
     * @return string
     */
    protected function whereSub($where)
    {
        $select = $this->compileSelect($where['query']);

        return $this->wrap($where['column']) .' ' .$where['operator'] ." ($select)";
    }

    /**
     * Compile a basic where clause.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereBasic($where)
    {
        $value = $this->parameter($where['value']);

        return $this->wrap($where['column']) .' ' .$where['operator'] .' ' .$value;
    }

    /**
     * Compile a "BETWEEN" where clause.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereBetween($where)
    {
        $between = $where['not'] ? 'NOT BETWEEN' : 'BETWEEN';

        return $this->wrap($where['column']) .' ' .$between .' ? AND ?';
    }

    /**
     * Compile a where exists clause.
     *
     * @param  \Nova\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereExists(Builder $query, $where)
    {
        return 'EXISTS ('.$this->compileSelect($where['query']).')';
    }

    /**
     * Compile a where exists clause.
     *
     * @param  \Nova\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereNotExists(Builder $query, $where)
    {
        return 'NOT EXISTS ('.$this->compileSelect($where['query']).')';
    }

    /**
     * Compile a "WHERE IN" clause.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereIn($where)
    {
        $values = $this->parameterize($where['values']);

        return $this->wrap($where['column']) .' IN (' .$values .')';
    }

    /**
     * Compile a "WHERE NOT IN" clause.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereNotIn($where)
    {
        $values = $this->parameterize($where['values']);

        return $this->wrap($where['column']) .' NOT IN (' .$values .')';
    }

    /**
     * Compile a where in sub-select clause.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereInSub($where)
    {
        $select = $this->compileSelect($where['query']);

        return $this->wrap($where['column']) .' IN (' .$select .')';
    }

    /**
     * Compile a where not in sub-select clause.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereNotInSub($where)
    {
        $select = $this->compileSelect($where['query']);

        return $this->wrap($where['column']) .' NOT IN (' .$select .')';
    }

    /**
     * Compile a "WHERE NULL" clause.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereNull($where)
    {
        return $this->wrap($where['column']) .' IS NULL';
    }

    /**
     * Compile a "WHERE NOT NULL" clause.
     *
     * @param  array  $where
     * @return string
     */
    protected function whereNotNull($where)
    {
        return $this->wrap($where['column']) .' IS NOT NULL';
    }

    /**
     * Compile a "WHERE DAY" clause.
     *
     * @param  \Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereDay(Builder $query, $where)
    {
        return $this->dateBasedWhere('day', $query, $where);
    }

    /**
     * Compile a "WHERE MONTH" clause.
     *
     * @param  \Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereMonth(Builder $query, $where)
    {
        return $this->dateBasedWhere('month', $query, $where);
    }

    /**
     * Compile a "WHERE YEAR" clause.
     *
     * @param  \Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereYear(Builder $query, $where)
    {
        return $this->dateBasedWhere('year', $query, $where);
    }

    /**
     * Compile a date based where clause.
     *
     * @param  string  $type
     * @param  \Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function dateBasedWhere($type, $where)
    {
        $value = $this->parameter($where['value']);

        return $type .'(' .$this->wrap($where['column']) .') ' .$where['operator'] .' ' .$value;
    }

    /**
     * Compile a raw "WHERE" clause.
     *
     * @param  \Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereRaw($where)
    {
        return $where['sql'];
    }

    /**
     * Compile the GROUP BY clause for a query.
     *
     * @param  Query   $query
     * @return string
     */
    protected function compileGroups(Builder $query, $groups)
    {
        return 'GROUP BY '.$this->columnize($groups);
    }

    /**
     * Compile the "HAVING" portions of the query.
     *
     * @param  \Database\Query\Builder  $query
     * @param  array  $havings
     * @return string
     */
    protected function compileHavings(Builder $query, $havings)
    {
        $sql = implode(' ', array_map(array($this, 'compileHaving'), $havings));

        return 'HAVING ' .preg_replace('/AND /', '', $sql, 1);
    }

    /**
     * Compile a single having clause.
     *
     * @param  array   $having
     * @return string
     */
    protected function compileHaving(array $having)
    {
        if ($having['type'] === 'Raw') {
            return $having['boolean'].' '.$having['sql'];
        }

        return $this->compileBasicHaving($having);
    }

    /**
     * Compile a basic having clause.
     *
     * @param  array   $having
     * @return string
     */
    protected function compileBasicHaving($having)
    {
        $column = $this->wrap($having['column']);

        $parameter = $this->parameter($having['value']);

        return 'AND ' .$column .' ' .$having['operator'] .' ' .$parameter;
    }

    /**
     * Compile the "ORDER BY" portions of the query.
     *
     * @param  \Database\Query\Builder  $query
     * @param  array  $orders
     * @return string
     */
    protected function compileOrders(Builder $query, $orders)
    {
        $me = $this;

        return 'ORDER BY ' .implode(', ', array_map(function($order) use ($me) {
            if (isset($order['sql'])) return $order['sql'];

            return $me->wrap($order['column']).' '.$order['direction'];
        }, $orders));
    }

    /**
     * Compile the "LIMIT" portions of the query.
     *
     * @param  \Database\Query\Builder  $query
     * @param  int  $limit
     * @return string
     */
    protected function compileLimit(Builder $query, $limit)
    {
        return 'LIMIT ' .(int) $limit;
    }

    /**
     * Compile the "OFFSET" portions of the query.
     *
     * @param  \Database\Query\Builder  $query
     * @param  int  $offset
     * @return string
     */
    protected function compileOffset(Builder $query, $offset)
    {
        return 'OFFSET ' .(int) $offset;
    }

    /**
     * Compile the "UNION" queries attached to the main query.
     *
     * @param  \Database\Query\Builder  $query
     * @return string
     */
    protected function compileUnions(Builder $query)
    {
        $sql = '';

        foreach ($query->unions as $union) {
            $sql .= $this->compileUnion($union);
        }

        return ltrim($sql);
    }

    /**
     * Compile a single "UNION" statement.
     *
     * @param  array  $union
     * @return string
     */
    protected function compileUnion(array $union)
    {
        $joiner = isset($union['all']) ? ' UNION ALL ' : ' UNION ';

        return $joiner .$union['query']->toSql();
    }

    /**
     * Compile an insert statement into SQL.
     *
     * @param  array  $values
     * @return string
     */
    public function compileInsert(array $values)
    {
        $table = $this->wrapTable($this->from);

        if (! is_array(reset($values))) {
            $values = array($values);
        }

        $columns = $this->columnize(array_keys(reset($values)));

        $parameters = $this->parameterize(reset($values));

        $value = array_fill(0, count($values), "($parameters)");

        $parameters = implode(', ', $value);

        return "INSERT INTO $table ($columns) VALUES $parameters";
    }

    /**
     * Compile an update statement into SQL.
     *
     * @param  array  $values
     * @return string
     */
    public function compileUpdate($values)
    {
        $table = $this->wrapTable($this->from);

        $columns = array();

        foreach ($values as $key => $value) {
            $columns[] = $this->wrap($key) .' = ' .$this->parameter($value);
        }

        $columns = implode(', ', $columns);

        if (isset($this->joins)) {
            $joins = ' ' .$this->compileJoins($this, $this->joins);
        } else {
            $joins = '';
        }

        $where = $this->compileWheres($this);

        $sql = trim("UPDATE {$table}{$joins} SET $columns $where");

        if (isset($this->orders)) {
            $sql .= ' ' .$this->compileOrders($this, $this->orders);
        }

        if (isset($this->limit)) {
            $sql .= ' ' .$this->compileLimit($this, $this->limit);
        }

        return rtrim($sql);
    }

    /**
     * Compile a delete statement into SQL.
     *
     * @return string
     */
    public function compileDelete()
    {
        $table = $this->wrapTable($this->from);

        $where = is_array($this->wheres) ? $this->compileWheres($this) : '';

        $sql = trim("DELETE FROM $table " .$where);

        if (isset($this->limit)) {
            $sql .= ' ' .$this->compileLimit($this, $this->limit);
        }

        return rtrim($sql);
    }

    /**
     * Compile a truncate table statement into SQL.
     *
     * @param  \Database\Query\Builder  $query
     * @return array
     */
    public function compileTruncate()
    {
        return array('TRUNCATE ' .$this->wrapTable($this->from) => array());
    }

    /**
     * Wrap a table in keyword identifiers.
     *
     * @param  string  $table
     * @return string
     */
    public function wrapTable($table)
    {
        return $this->wrap($this->connection->getTablePrefix() .$table);
    }

    /**
     * Wrap a value in keyword identifiers.
     *
     * @param  string  $value
     * @return string
     */
    public function wrap($value)
    {
        if (strpos(strtolower($value), ' as ') !== false) {
            $segments = explode(' ', $value);

            return $this->wrap($segments[0]) .' AS ' .$this->wrap($segments[2]);
        }

        $wrapped = array();

        $segments = explode('.', $value);

        foreach ($segments as $key => $segment) {
            if (($key == 0) && (count($segments) > 1)) {
                $wrapped[] = $this->wrapTable($segment);
            } else {
                $wrapped[] = $this->wrapValue($segment);
            }
        }

        return implode('.', $wrapped);
    }

    /**
     * Wrap an array of values.
     *
     * @param  array  $values
     * @return array
     */
    public function wrapArray(array $values)
    {
        return array_map(array($this, 'wrap'), $values);
    }

    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapValue($value)
    {
        return ($value !== '*') ? sprintf('`%s`', $value) : $value;
    }

    /**
     * Concatenate an array of segments, removing empties.
     *
     * @param  array   $segments
     * @return string
     */
    protected function concatenate($segments)
    {
        return implode(' ', array_filter($segments, function($value)
        {
            return ! empty($value);
        }));
    }

    /**
     * Remove the leading boolean from a statement.
     *
     * @param  string  $value
     * @return string
     */
    protected function removeLeadingBoolean($value)
    {
        return preg_replace('/AND |OR /', '', $value, 1);
    }

    /**
     * Create query parameter place-holders for an array.
     *
     * @param  array   $values
     * @return string
     */
    public function parameterize(array $values)
    {
        return implode(', ', array_map(array($this, 'parameter'), $values));
    }

    /**
     * Get the appropriate query parameter place-holder for a value.
     *
     * @param  mixed   $value
     * @return string
     */
    public function parameter($value)
    {
        return ($value instanceof Expression) ? $value->get() : '?';
    }

    /**
     * Convert an array of column names into a delimited string.
     *
     * @param  array   $columns
     * @return string
     */
    public function columnize(array $columns)
    {
        return implode(', ', array_map(array($this, 'wrap'), $columns));
    }

    /**
     * Get the format for database stored dates.
     *
     * @return string
     */
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * Get the grammar's table prefix.
     *
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * Set the grammar's table prefix.
     *
     * @param  string  $prefix
     * @return $this
     */
    public function setTablePrefix($prefix)
    {
        $this->tablePrefix = $prefix;

        return $this;
    }

}
