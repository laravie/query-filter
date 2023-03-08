<?php

namespace Laravie\QueryFilter\Filters;

use Illuminate\Contracts\Database\Query\Expression;
use Laravie\QueryFilter\Contracts\Keyword\AsLowerCase;
use Laravie\QueryFilter\SearchFilter;

class JsonFieldSearch extends SearchFilter implements AsLowerCase
{
    /**
     * Construct a new JSON Field Search.
     */
    public function __construct(
        protected Expression|string $path
    ) {
        //
    }

    /**
     * Apply JSON field search queries.
     *
     * @param  \Illuminate\Contracts\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $query
     * @param  array<int, string>  $keywords
     * @return \Illuminate\Contracts\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function apply($query, array $keywords, string $likeOperator, string $whereOperator)
    {
        /** @var string $path */
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
