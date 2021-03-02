<?php

namespace Laravie\QueryFilter\Contracts;

interface SearchFilter
{
    /**
     * Apply search queries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function apply($query, Keyword $keywords, string $likeOperator, string $whereOperator);
}
