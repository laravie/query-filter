<?php

namespace Laravie\QueryFilter;

use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use RuntimeException;

abstract class SearchFilter implements Contracts\SearchFilter
{
    /**
     * Validate $query.
     *
     * @param  mixed  $query
     * @return $this
     *
     * @throws \RuntimeException
     */
    public function validate($query)
    {
        if ($this instanceof Contracts\Filter\RequiresEloquent) {
            $this->validateEloquentQueryBuilder($query);
        } elseif ($this instanceof Contracts\Filter\RequiresFluent) {
            $this->validateFluentQueryBuilder($query);
        }

        return $this;
    }

    /**
     * Validate $query is an instance of Eloquent Query Builder.
     *
     * @param  mixed  $query
     *
     * @throws \RuntimeException
     */
    protected function validateEloquentQueryBuilder($query): void
    {
        if (! $query instanceof EloquentQueryBuilder) {
            throw new RuntimeException('Unable to use '.class_basename($this).' when $query is not an instance of '.EloquentQueryBuilder::class);
        }
    }

    /**
     * Validate $query is an instance of Fluent Query Builder.
     *
     * @param  mixed  $query
     *
     * @throws \RuntimeException
     */
    protected function validateFluentQueryBuilder($query): void
    {
        if (! $query instanceof QueryBuilder) {
            throw new RuntimeException('Unable to use '.class_basename($this).' when $query is not an instance of '.QueryBuilder::class);
        }
    }
}
