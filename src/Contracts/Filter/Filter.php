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
     * @param  \Illuminate\Contracts\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $query
     * @param  array<int, string>  $keywords
     * @return \Illuminate\Contracts\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function apply($query, array $keywords, string $likeOperator, string $whereOperator);
}
