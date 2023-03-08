<?php

namespace Laravie\QueryFilter\Filters;

use Laravie\QueryFilter\Contracts\Filter\RequiresEloquent;
use Laravie\QueryFilter\SearchFilter;

class MorphRelationSearch extends SearchFilter implements RequiresEloquent
{
    /**
     * Construct new Morph Related Search.
     *
     * @param  array<int, string>  $types
     */
    public function __construct(
        protected string $relation,
        protected string $column,
        protected array $types = []
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
        $types = ! empty($this->types) ? $this->types : '*';

        $query->{$whereOperator.'HasMorph'}($this->relation, $types, function ($query) use ($keywords, $likeOperator) {
            return (new FieldSearch($this->column))->validate($query)->apply(
                $query, $keywords, $likeOperator, 'where'
            );
        });

        return $query;
    }
}
