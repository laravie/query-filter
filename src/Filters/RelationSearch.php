<?php

namespace Laravie\QueryFilter\Filters;

use Laravie\QueryFilter\Contracts\Filter\RequiresEloquent;
use Laravie\QueryFilter\SearchFilter;

class RelationSearch extends SearchFilter implements RequiresEloquent
{
    /**
     * Relation name.
     *
     * @var string
     */
    protected $relation;

    /**
     * Related column used for search.
     *
     * @var \Illuminate\Database\Query\Expression|string
     */
    protected $column;

    /**
     * Construct new Related Search.
     *
     * @param  \Illuminate\Database\Query\Expression|string  $column
     */
    public function __construct(string $relation, $column)
    {
        $this->relation = $relation;
        $this->column = $column;
    }

    /**
     * Apply relation search queries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
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
