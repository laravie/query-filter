<?php

namespace Laravie\QueryFilter\Filters;

use Laravie\QueryFilter\Search;
use Laravie\QueryFilter\Contracts\Keyword;

class JsonFieldSearch extends Search
{
    /**
     * Apply JSON field search queries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function apply($query, Keyword $keywords, string $likeOperator, string $whereOperator)
    {
        [$field, $path] = $this->field->wrapJsonFieldAndPath();

        return $query->{$whereOperator}(static function ($query) use ($field, $path, $keywords, $likeOperator) {
            foreach ($keywords->allLowerCased() as $keyword) {
                $query->orWhereRaw(
                    "lower({$field}->'\$.{$path}') {$likeOperator} ?", [$keyword]
                );
            }
        });
    }
}
