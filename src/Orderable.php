<?php

namespace Laravie\QueryFilter;

use Illuminate\Support\Str;
use Illuminate\Support\Traits\Tappable;

class Orderable
{
    use Tappable;

    /**
     * Ordered column name.
     *
     * @var \Laravie\QueryFilter\Column
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
     * @var array{only?: string|array, except?: string|array}
     */
    protected $config = [];

    /**
     * Construct a new Ordered Query.
     *
     * @param  array{only?: string|array, except?: string|array}  $config
     */
    public function __construct(?string $column, string $direction = 'asc', array $config = [])
    {
        $this->column = new Column($this->sanitizeColumnName($column ?? ''));
        $this->direction = $this->sanitizeDirection($direction);
        $this->config = $config;
    }

    /**
     * Apply ordered to query.
     *
     * @param  \Illuminate\Contracts\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Contracts\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function apply($query)
    {
        return $this->validate()
            ? $query->orderBy($this->column->getValue(), $this->direction)
            : $query;
    }

    /**
     * Validate requirement before applying to query.
     */
    protected function validate(): bool
    {
        if (! $this->column->validate()) {
            return false;
        }

        return $this->column->accepted(
            (array) ($this->config['only'] ?? []),
            (array) ($this->config['except'] ?? [])
        );
    }

    /**
     * Sanitized column name.
     */
    protected function sanitizeColumnName(string $column): string
    {
        return \in_array($column, ['created', 'updated', 'deleted'])
            ? "{$column}_at"
            : $column;
    }

    /**
     * Sanitized ordered direction.
     */
    protected function sanitizeDirection(string $direction): string
    {
        $direction = Str::upper($direction);

        return \in_array($direction, ['ASC', 'DESC']) ? $direction : 'ASC';
    }
}
