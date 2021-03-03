<?php

namespace Laravie\QueryFilter\Filters;

use Laravie\QueryFilter\SearchFilter;
use Laravie\QueryFilter\Contracts\Keyword;

class RelationSearch extends SearchFilter
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
    public function apply($query, Keyword $keywords, string $likeOperator, string $whereOperator)
    {
        $this->validateEloquentQueryBuilder($query);

        $query->{$whereOperator.'Has'}($this->relation, function ($query) use ($keywords, $likeOperator) {
            return (new FieldSearch($this->column))->apply($query, $keywords, $likeOperator, 'where');
        });

        return $query;
    }
}
