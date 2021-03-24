<?php

namespace Laravie\QueryFilter\Filters;

use Laravie\QueryFilter\SearchFilter;

class MorphRelationSearch extends SearchFilter
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
     * Available morph types.
     *
     * @var array
     */
    protected $types = [];

    /**
     * Construct new Morph Related Search.
     *
     * @param  \Illuminate\Database\Query\Expression|string  $column
     */
    public function __construct(string $relation, $column, array $types = [])
    {
        $this->relation = $relation;
        $this->column = $column;
        $this->types = $types;
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
        $this->validateEloquentQueryBuilder($query);

        $types = ! empty($this->types) ? $this->types : '*';

        $query->{$whereOperator.'HasMorph'}($this->relation, $types, function ($query) use ($keywords, $likeOperator) {
            return (new FieldSearch($this->column))->apply(
                $query, $keywords, $likeOperator, 'where'
            );
        });

        return $query;
    }
}
