<?php

namespace Laravie\QueryFilter;

use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;

/**
 * Get connection type from Query Builder.
 *
 * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
 */
function connection_type($query): string
{
    return $query instanceof EloquentQueryBuilder
            ? $query->getModel()->getConnection()->getDriverName()
            : $query->getConnection()->getDriverName();
}
