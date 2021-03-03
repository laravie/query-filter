<?php

namespace Laravie\QueryFilter;

use RuntimeException;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;

abstract class SearchFilter implements Contracts\SearchFilter
{
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
