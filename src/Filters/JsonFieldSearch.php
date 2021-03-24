<?php

namespace Laravie\QueryFilter\Filters;

use Illuminate\Database\Query\Expression;
use Laravie\QueryFilter\Contracts\Keyword\AsLowerCase;
use Laravie\QueryFilter\SearchFilter;

class JsonFieldSearch extends SearchFilter implements AsLowerCase
{
    /**
     * JSON path.
     *
     * @var \Illuminate\Database\Query\Expression|string
     */
    protected $path;

    /**
     * Construct a new JSON Field Search.
     *
     * @param  \Illuminate\Database\Query\Expression|string  $path
     */
    public function __construct($path)
    {
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
        $path = $query->getGrammar()->wrap($this->path);

        return $query->{$whereOperator}(function ($query) use ($path, $keywords, $likeOperator) {
            foreach ($keywords as $keyword) {
                $query->orWhereRaw(
                    "lower({$path}) {$likeOperator} ?", [$keyword]
                );
            }
        });
    }
}
