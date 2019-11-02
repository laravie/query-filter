<?php

namespace Laravie\QueryFilter;

use Orchestra\Support\Str;

class OrderedQuery
{
    /**
     * Ordered column name.
     *
     * @var string
     */
    protected $column;

    /**
     * Ordered direction.
     *
     * @var string
     */
    protected $direction;

    /**
     * Configurations.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Construct a new Ordered Query.
     *
     * @param string  $column
     * @param string  $direction
     * @param array  $config
     */
    public function __construct(string $column, string $direction = 'asc', array $config = [])
    {
        $this->column = $this->sanitizeColumnName($column);
        $this->direction = $this->sanitizeDirection($direction);
        $this->config = $config;
    }

    /**
     * Apply ordered to query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function apply($query)
    {
        return $this->validated()
            ? $query->orderBy($this->column, $this->direction)
            : $query;
    }

    /**
     * Validate requirement before applying to query.
     *
     * @return bool
     */
    protected function validated(): bool
    {
        if (empty($this->column) || ! Str::validateColumnName($this->column)) {
            return false;
        }

        $only = $this->config['only'] ?? [];
        $except = $this->config['except'] ?? [];

        if ((! empty($only) && ! \in_array($this->column, (array) $only))
            || (! empty($except) && \in_array($this->column, (array) $except))
        ) {
            return false;
        }

        return true;
    }

    /**
     * Sanitized column name.
     *
     * @param  string  $column
     *
     * @return string
     */
    protected function sanitizeColumnName(string $column): string
    {
        return \in_array($column, ['created', 'updated', 'deleted'])
            ? "{$column}_at"
            : $column;
    }

    /**
     * Sanitized ordered direction.
     *
     * @param  string  $direction
     *
     * @return string
     */
    protected function sanitizeDirection(string $direction): string
    {
        $direction = Str::upper($direction);

        return \in_array($direction, ['ASC', 'DESC']) ? $direction : 'ASC';
    }
}
