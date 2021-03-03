<?php

namespace Laravie\QueryFilter\Filters;

use Laravie\QueryFilter\Value\Field;
use Laravie\QueryFilter\SearchFilter;
use Laravie\QueryFilter\Contracts\Keyword;

class MorphRelationSearch extends SearchFilter
{
    /**
     * Relation name.
     *
     * @var string
     */
    protected $name;

    /**
     * Related column used for search.
     *
     * @var string
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
     */
    public function __construct(string $name, string $column, array $types = [])
    {
        $this->name = $name;
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
    public function apply($query, Keyword $keywords, string $likeOperator, string $whereOperator)
    {
        $this->validateEloquentQueryBuilder($query);

        $types = ! empty($this->types) ? '*' : $this->types;

        $query->{$whereOperator.'HasMorph'}($this->name, $types, function ($query) use ($keywords, $likeOperator) {
            return (new FieldSearch())
                ->field(new Field($this->column))
                ->apply($query, $keywords, $likeOperator, 'where');
        });

        return $query;
    }
}
