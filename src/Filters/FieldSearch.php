<?php

namespace Laravie\QueryFilter\Filters;

use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Laravie\QueryFilter\SearchFilter;

class FieldSearch extends SearchFilter
{
    /**
     * Construct a new Field Search.
     */
    public function __construct(
        protected string $column
    ) {
        //
    }

    /**
     * Apply generic field search queries.
     *
     * @param  \Illuminate\Contracts\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $query
     * @param  array<int, string>  $keywords
     * @return \Illuminate\Contracts\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function apply($query, array $keywords, string $likeOperator, string $whereOperator)
    {
        $column = $query instanceof EloquentQueryBuilder
            ? $query->qualifyColumn((string) $this->column)
            : $this->column;

        if (\count($keywords) > 1) {
            return $query->{$whereOperator}(static function ($query) use ($column, $keywords, $likeOperator) {
                foreach ($keywords as $keyword) {
                    $query->orWhere((string) $column, $likeOperator, $keyword);
                }
            });
        } elseif (! empty($keywords)) {
            return $query->{$whereOperator}((string) $column, $likeOperator, $keywords[0]);
        }

        return $query;
    }
}
