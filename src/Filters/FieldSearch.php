<?php

namespace Laravie\QueryFilter\Filters;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Laravie\QueryFilter\SearchFilter;

class FieldSearch extends SearchFilter
{
    /**
     * Construct a new Field Search.
     */
    public function __construct(
        protected Expression|string $column
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
        $column = $this->column instanceof Expression ? $query->getGrammar()->wrap($this->column) : $this->column;

        $attribute = $query instanceof EloquentQueryBuilder
            ? $query->qualifyColumn($column)
            : $column;

        if (\count($keywords) > 1) {
            return $query->{$whereOperator}(static function ($query) use ($attribute, $keywords, $likeOperator) {
                foreach ($keywords as $keyword) {
                    $query->orWhere($attribute, $likeOperator, $keyword);
                }
            });
        } elseif (! empty($keywords)) {
            return $query->{$whereOperator}($attribute, $likeOperator, $keywords[0]);
        }

        return $query;
    }
}
