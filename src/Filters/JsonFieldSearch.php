<?php

namespace Laravie\QueryFilter\Filters;

use Laravie\QueryFilter\Contracts\Keyword\AsLowerCase;
use Laravie\QueryFilter\SearchFilter;

class JsonFieldSearch extends SearchFilter implements AsLowerCase
{
    /**
     * Column name.
     *
     * @var string
     */
    protected $column;

    /**
     * JSON path.
     *
     * @var string
     */
    protected $path;

    /**
     * Construct a new JSON Field Search.
     */
    public function __construct(string $column, string $path)
    {
        $this->column = $column;
        $this->path = $path;
    }

    /**
     * Apply JSON field search queries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function apply($query, array $keywords, string $likeOperator, string $whereOperator)
    {
        return $query->{$whereOperator}(function ($query) use ($keywords, $likeOperator) {
            foreach ($keywords as $keyword) {
                $query->orWhereRaw(
                    "lower({$this->column}->'\$.{$this->path}') {$likeOperator} ?", [$keyword]
                );
            }
        });
    }
}
