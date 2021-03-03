<?php

namespace Laravie\QueryFilter\Filters;

use Laravie\QueryFilter\SearchFilter;
use Laravie\QueryFilter\Contracts\Keyword;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;

class FieldSearch extends SearchFilter
{
    /**
     * Apply generic field search queries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function apply($query, Keyword $keywords, string $likeOperator, string $whereOperator)
    {
        $field = $query instanceof EloquentQueryBuilder
            ? $query->qualifyColumn((string) $this->field)
            : $this->field;

        return $query->{$whereOperator}(static function ($query) use ($field, $keywords, $likeOperator) {
            foreach ($keywords->all() as $keyword) {
                $query->orWhere((string) $field, $likeOperator, $keyword);
            }
        });
    }
}
