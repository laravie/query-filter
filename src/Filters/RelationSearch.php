<?php

namespace Laravie\QueryFilter\Filters;

use Laravie\QueryFilter\Search;

class RelationSearch extends Search
{
    /**
     * Apply relation search queries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($query, array $keywords, string $likeOperator, string $whereOperator)
    {
        [$relation, $field] = $this->field->wrapRelationNameAndField();

        $query->{$whereOperator.'Has'}($relation, function ($query) use ($field, $keywords, $likeOperator) {
            return (new FieldSearch())->field($field)->apply($query, $keywords, $likeOperator, 'where');
        });

        return $query;
    }
}