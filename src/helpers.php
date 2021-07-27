<?php

namespace Laravie\QueryFilter;

use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;

/**
 * Get connection type from Query Builder.
 *
 * @param  \Illuminate\Contracts\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
 */
function connection_type($query): string
{
    return $query instanceof EloquentQueryBuilder
            ? $query->getModel()->getConnection()->getDriverName()
            : $query->getConnection()->getDriverName();
}

/**
 * Get like operator based on connection type.
 */
function like_operator(string $connectionType): string
{
    return $connectionType == 'pgsql' ? 'ilike' : 'like';
}
