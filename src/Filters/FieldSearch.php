<?php

namespace Laravie\QueryFilter\Filters;

use Laravie\QueryFilter\SearchFilter;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;

class FieldSearch extends SearchFilter
{
    /**
     * Column name.
     *
     * @var \Illuminate\Database\Query\Expression|string
     */
    protected $column;

    /**
     * Construct a new Field Search.
     *
     * @param  \Illuminate\Database\Query\Expression|string  $column
     */
    public function __construct($column)
    {
        $this->column = $column;
    }

    /**
     * Apply generic field search queries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @param  array  $keywords
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function apply($query, array $keywords, string $likeOperator, string $whereOperator)
    {
        $column = $query instanceof EloquentQueryBuilder
            ? $query->qualifyColumn((string) $this->column)
            : $this->column;

        if (count($keywords) > 1) {
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
