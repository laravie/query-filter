<?php

namespace Laravie\QueryFilter;

use Illuminate\Support\Traits\Tappable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class Searchable
{
    use Concerns\ConditionallySearchingWildcard,
        Concerns\SearchingWildcard,
        Tappable;

    /**
     * Search keyword.
     *
     * @var \Laravie\QueryFilter\Value\Keyword
     */
    protected $keyword;

    /**
     * Search columns.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * Construct a new Search Query.
     */
    public function __construct(?string $keyword, array $columns = [])
    {
        $this->keyword = new Value\Keyword($keyword ?? '');
        $this->columns = \array_filter($columns);
    }

    /**
     * Get search keyword.
     */
    public function searchKeyword(): Value\Keyword
    {
        return $this->keyword;
    }

    /**
     * Apply search to query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function apply($query)
    {
        if (! $this->keyword->validate() || empty($this->columns)) {
            return $query;
        }

        $connectionType = $query instanceof EloquentBuilder
            ? $query->getModel()->getConnection()->getDriverName()
            : $query->getConnection()->getDriverName();

        $likeOperator = $connectionType == 'pgsql' ? 'ilike' : 'like';

        $query->where(function ($query) use ($likeOperator) {
            foreach ($this->columns as $column) {
                $this->queryOnColumn($query, Value\Field::make($column), $likeOperator);
            }
        });

        return $query;
    }

    /**
     * Build wildcard query filter for field using where or orWhere.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function queryOnColumn($query, Value\Field $column, string $likeOperator = 'like')
    {
        if ($column->isExpression()) {
            return $this->queryOnColumnUsing($query, new Value\Field($column->getValue()), $likeOperator, 'orWhere');
        } elseif ($column->isRelationSelector() && $query instanceof EloquentBuilder) {
            [$relation, $column] = $column->wrapRelationNameAndField();

            return $query->orWhereHas($relation, function ($query) use ($column, $likeOperator) {
                $this->queryOnColumnUsing($query, $column, $likeOperator, 'where');
            });
        }

        return $this->queryOnColumnUsing($query, $column, $likeOperator, 'orWhere');
    }

    /**
     * Build wildcard query filter for column using where or orWhere.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function queryOnColumnUsing(
        $query,
        Value\Field $column,
        string $likeOperator,
        string $whereOperator = 'where'
    ) {
        if (! $column->validate()) {
            return $query;
        } elseif ($column->isJsonPathSelector()) {
            return $this->queryOnJsonColumnUsing($query, $column, $likeOperator, $whereOperator);
        }

        $wildcardSearching = $column->wildcardSearching ?? $this->wildcardSearching ?? true;

        $keywords = $this->keyword->all(
            $this->wildcardCharacter, $this->wildcardReplacement, $wildcardSearching
        );

        if ($query instanceof EloquentBuilder) {
            $column = $query->qualifyColumn((string) $column);
        }

        return $query->{$whereOperator}(static function ($query) use ($column, $keywords, $likeOperator) {
            foreach ($keywords as $keyword) {
                $query->orWhere((string) $column, $likeOperator, $keyword);
            }
        });
    }

    /**
     * Build wildcard query filter for JSON column using where or orWhere.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function queryOnJsonColumnUsing(
        $query,
        Value\Field $column,
        string $likeOperator,
        string $whereOperator = 'where'
    ) {
        $wildcardSearching = $column->wildcardSearching ?? $this->wildcardSearching ?? true;

        $keywords = $this->keyword->allLowerCased(
            $this->wildcardCharacter, $this->wildcardReplacement, $wildcardSearching
        );

        [$field, $path] = $column->wrapJsonFieldAndPath();

        return $query->{$whereOperator}(static function ($query) use ($field, $path, $keywords, $likeOperator) {
            foreach ($keywords as $keyword) {
                $query->orWhereRaw(
                    "lower({$field}->'\$.{$path}') {$likeOperator} ?", [$keyword]
                );
            }
        });
    }
}
