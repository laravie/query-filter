<?php

namespace Laravie\QueryFilter;

use Illuminate\Support\Traits\Tappable;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;

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
        $this->keyword = $keyword ?? '';
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
        $keywords = $this->searchKeyword();

        if (! $keywords->validate() || empty($this->fields)) {
            return $query;
        }

        $keywords->wildcardCharacter($this->wildcardCharacter)
            ->wildcardReplacement($this->wildcardReplacement)
            ->wildcardSearching($this->wildcardSearching ?? true);

        $connectionType = $query instanceof EloquentQueryBuilder
            ? $query->getModel()->getConnection()->getDriverName()
            : $query->getConnection()->getDriverName();

        $likeOperator = $connectionType == 'pgsql' ? 'ilike' : 'like';

        [$filters, $fields] = \collect($this->fields)->partition(function ($field) {
            return $field instanceof Contracts\SearchFilter;
        });

        $query->where(function ($query) use ($fields, $filters, $keywords, $likeOperator) {
            foreach ($filters as $filter) {
                $filter->apply($query, $keywords, $likeOperator, 'orWhere');
            }

            foreach ($fields as $field) {
                $this->queryOnColumn($query, Value\Field::make($field), $likeOperator, 'orWhere');
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
    protected function queryOnColumn(
        $query,
        Value\Field $field,
        string $likeOperator = 'like',
        string $whereOperator = 'orWhere'
    ) {
        if ($field->isExpression()) {
            return $this->queryOnColumnUsing($query, new Value\Field($field->getValue()), $likeOperator, $whereOperator);
        } elseif ($field->isRelationSelector() && $query instanceof EloquentQueryBuilder) {
            return $this->queryOnColumnUsingRelation($query, $field, $likeOperator);
        }

        return $this->queryOnColumnUsing($query, $field, $likeOperator, $whereOperator);
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

        return (new Filters\FieldSearch($field->getOriginalValue()))->apply(
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
        [$column, $path] = $field->wrapJsonFieldAndPath();

        return (new Filters\JsonFieldSearch($column, $path))->apply(
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
        EloquentQueryBuilder $query,
        Value\Field $field,
        string $likeOperator
    ): EloquentQueryBuilder {
        [$relation, $column] = $field->wrapRelationNameAndField();

        return (new Filters\RelationSearch($relation, $column))->apply(
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
