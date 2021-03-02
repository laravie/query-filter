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
    protected $fields = [];

    /**
     * Construct a new Search Query.
     */
    public function __construct(?string $keyword, array $fields = [])
    {
        $this->keyword = new Value\Keyword($keyword ?? '');
        $this->fields = \array_filter($fields);
    }

    /**
     * Get search keyword.
     */
    public function searchKeyword(): Value\Keyword
    {
        return new Value\Keyword($this->keyword);
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
        if (! $this->keyword->validate() || empty($this->fields)) {
            return $query;
        }

        $connectionType = $query instanceof EloquentBuilder
            ? $query->getModel()->getConnection()->getDriverName()
            : $query->getConnection()->getDriverName();

        $likeOperator = $connectionType == 'pgsql' ? 'ilike' : 'like';

        [$filters, $fields] = \collect($this->fields)->partition(function ($field) {
            return $field instanceof Contracts\Search;
        });

        $query->where(function ($query) use ($fields, $filters, $likeOperator) {
            $keywords = $this->searchKeyword()
                    ->wildcardCharacter($this->wildcardCharacter)
                    ->wildcardReplacement($this->wildcardReplacement)
                    ->wildcardSearching($this->wildcardSearching ?? true);

            foreach ($filters as $filter) {
                $filter->apply($query, $keywords, $likeOperator);
            }

            foreach ($fields as $field) {
                $this->queryOnColumn($query, Value\Field::make($field), $likeOperator);
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
    protected function queryOnColumn($query, Value\Field $field, string $likeOperator = 'like')
    {
        if ($field->isExpression()) {
            return $this->queryOnColumnUsing($query, new Value\Field($field->getValue()), $likeOperator, 'orWhere');
        } elseif ($field->isRelationSelector() && $query instanceof EloquentBuilder) {
            return $this->queryOnColumnUsingRelation($query, $field, $likeOperator);
        }

        return $this->queryOnColumnUsing($query, $field, $likeOperator, 'orWhere');
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
        Value\Field $field,
        string $likeOperator,
        string $whereOperator = 'where'
    ) {
        if (! $field->validate()) {
            return $query;
        } elseif ($field->isJsonPathSelector()) {
            return $this->queryOnJsonColumnUsing($query, $field, $likeOperator, 'orWhere');
        }

        return (new Filters\FieldSearch())->field($field)->apply(
            $query,
            $this->searchKeyword()
                ->wildcardCharacter($this->wildcardCharacter)
                ->wildcardReplacement($this->wildcardReplacement)
                ->wildcardSearching($field->wildcardSearching ?? $this->wildcardSearching ?? true),
            $likeOperator,
            $whereOperator
        );
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
        Value\Field $field,
        string $likeOperator,
        string $whereOperator = 'where'
    ) {
        return (new Filters\JsonFieldSearch())
            ->field($field)
            ->apply(
                $query,
                $this->searchKeyword()
                    ->wildcardCharacter($this->wildcardCharacter)
                    ->wildcardReplacement($this->wildcardReplacement)
                    ->wildcardSearching($field->wildcardSearching ?? $this->wildcardSearching ?? true),
                $likeOperator,
                $whereOperator
            );
    }

        /**
     * Build wildcard query filter for column using where on relation.
     */
    protected function queryOnColumnUsingRelation(
        EloquentBuilder $query,
        Value\Field $field,
        string $likeOperator
    ): EloquentBuilder {
        return (new Filters\RelationSearch())
            ->field($field)
            ->apply(
                $query,
                $this->searchKeyword()
                    ->wildcardCharacter($this->wildcardCharacter)
                    ->wildcardReplacement($this->wildcardReplacement)
                    ->wildcardSearching($field->wildcardSearching ?? $this->wildcardSearching ?? true),
                $likeOperator,
                'orWhere'
            );
    }
}
