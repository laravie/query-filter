<?php

namespace Laravie\QueryFilter\Filters;

use Laravie\QueryFilter\Contracts\Filter\RequiresEloquent;
use Laravie\QueryFilter\SearchFilter;

class RelationSearch extends SearchFilter implements RequiresEloquent
{
    /**
     * Construct new Related Search.
     */
    public function __construct(
        protected string $relation,
        protected string $column
    ) {
        //
    }

    /**
     * Apply relation search queries.
     *
     * @param  \Illuminate\Contracts\Database\Eloquent\Builder  $query
     * @param  array<int, string>  $keywords
     * @return \Illuminate\Contracts\Database\Eloquent\Builder
     */
    public function apply($query, array $keywords, string $likeOperator, string $whereOperator)
    {
        $query->{$whereOperator.'Has'}($this->relation, function ($query) use ($keywords, $likeOperator) {
            return (new FieldSearch($this->column))->validate($query)->apply(
                $query, $keywords, $likeOperator, 'where'
            );
        });

        return $query;
    }
}
