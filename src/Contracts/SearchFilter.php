<?php

namespace Laravie\QueryFilter\Contracts;

interface SearchFilter
{
    /**
     * Apply search queries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @param  array|mixed  $keywords
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function apply($query, array $keywords, string $likeOperator, string $whereOperator);
}
