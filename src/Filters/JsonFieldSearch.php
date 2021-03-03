<?php

namespace Laravie\QueryFilter\Filters;

use Laravie\QueryFilter\SearchFilter;
use Laravie\QueryFilter\Contracts\Keyword;

class JsonFieldSearch extends SearchFilter
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
    public function apply($query, Keyword $keywords, string $likeOperator, string $whereOperator)
    {
        return $query->{$whereOperator}(function ($query) use ($keywords, $likeOperator) {
            foreach ($keywords->allLowerCased() as $keyword) {
                $query->orWhereRaw(
                    "lower({$this->column}->'\$.{$this->path}') {$likeOperator} ?", [$keyword]
                );
            }
        });
    }
}
