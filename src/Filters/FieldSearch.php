<?php

namespace Laravie\QueryFilter\Filters;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Laravie\QueryFilter\Contracts\Keyword;
use Laravie\QueryFilter\Search;

class FieldSearch extends Search
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
        $field = $query instanceof EloquentBuilder
            ? $query->qualifyColumn((string) $this->field)
            : $this->field;

        return $query->{$whereOperator}(static function ($query) use ($field, $keywords, $likeOperator) {
            foreach ($keywords->all() as $keyword) {
                $query->orWhere((string) $field, $likeOperator, $keyword);
            }
        });
    }
}
