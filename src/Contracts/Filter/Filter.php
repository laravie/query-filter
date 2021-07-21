<?php

namespace Laravie\QueryFilter\Contracts\Filter;

/**
 * @method $this validate(mixed $query)
 */
interface Filter
{
    /**
     * Apply search queries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @param  array  $keywords
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function apply($query, array $keywords, string $likeOperator, string $whereOperator);
}
