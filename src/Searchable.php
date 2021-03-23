<?php

namespace Laravie\QueryFilter;

use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Traits\Tappable;

class Searchable
{
    use Concerns\ConditionallySearchingWildcard,
        Concerns\SearchingWildcard,
        Tappable;

    /**
     * Search keyword.
     *
     * @var \Laravie\QueryFilter\Keyword
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
    public function searchKeyword(): Keyword
    {
        return new Keyword($this->keyword);
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

        $likeOperator = connection_type($query) == 'pgsql' ? 'ilike' : 'like';

        [$filters, $fields] = \collect($this->fields)->partition(static function ($field) {
            return $field instanceof Contracts\SearchFilter;
        });

        $query->where(function ($query) use ($fields, $filters, $keywords, $likeOperator) {
            foreach ($filters as $filter) {
                $filter->apply($query, $keywords->handle($filter), $likeOperator, 'orWhere');
            }

            foreach ($fields as $field) {
                $this->queryOnColumn($query, Field::make($field), $likeOperator, 'orWhere');
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
        Field $field,
        string $likeOperator = 'like',
        string $whereOperator = 'orWhere'
    ) {
        if ($field->isExpression()) {
            return $this->queryOnColumnUsing($query, new Field($field->getValue()), $likeOperator, $whereOperator);
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
        Field $field,
        string $likeOperator,
        string $whereOperator = 'where'
    ) {
        if (! $field->validate()) {
            return $query;
        } elseif ($field->isJsonPathSelector()) {
            return $this->queryOnJsonColumnUsing($query, $field, $likeOperator, 'orWhere');
        }

        \tap($this->getFieldSearchFilter($field), function ($filter) use ($field, $query, $likeOperator, $whereOperator) {
            $filter->apply(
                $query,
                $this->searchKeyword()
                    ->wildcardCharacter($this->wildcardCharacter)
                    ->wildcardReplacement($this->wildcardReplacement)
                    ->wildcardSearching($field->wildcardSearching ?? $this->wildcardSearching ?? true)
                    ->handle($filter),
                $likeOperator,
                $whereOperator
            );
        });

        return $query;
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
        Field $field,
        string $likeOperator,
        string $whereOperator = 'where'
    ) {
        \tap($this->getJsonFieldSearchFilter($field, $query), function ($filter) use ($field, $query, $likeOperator, $whereOperator) {
            $filter->apply(
                $query,
                $this->searchKeyword()
                    ->wildcardCharacter($this->wildcardCharacter)
                    ->wildcardReplacement($this->wildcardReplacement)
                    ->wildcardSearching($field->wildcardSearching ?? $this->wildcardSearching ?? true)
                    ->handle($filter),
                $likeOperator,
                $whereOperator
            );
        });

        return $query;
    }

    /**
     * Build wildcard query filter for column using where on relation.
     */
    protected function queryOnColumnUsingRelation(
        EloquentQueryBuilder $query,
        Field $field,
        string $likeOperator
    ): EloquentQueryBuilder {
        \tap($this->getRelationSearchFilter($field), function ($filter) use ($field, $query, $likeOperator) {
            $filter->apply(
                $query,
                $this->searchKeyword()
                    ->wildcardCharacter($this->wildcardCharacter)
                    ->wildcardReplacement($this->wildcardReplacement)
                    ->wildcardSearching($field->wildcardSearching ?? $this->wildcardSearching ?? true)
                    ->handle($filter),
                $likeOperator,
                'orWhere'
            );
        });

        return $query;
    }

    /**
     * Get Field Search Filter.
     *
     * @param  \Laravie\QueryFilter\Field  $field
     */
    protected function getFieldSearchFilter(Field $field): Contracts\SearchFilter
    {
        return new Filters\FieldSearch($field->getOriginalValue());
    }

    /**
     * Get JSON Field Search Filter.
     *
     * @param  \Laravie\QueryFilter\Field  $field
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    protected function getJsonFieldSearchFilter(Field $field, $query): Contracts\SearchFilter
    {
        return new Filters\JsonFieldSearch(
            new Expression(
                $query->getGrammar()->wrap($field->getOriginalValue())
            )
        );
    }

    /**
     * Get Relation Search Filter.
     *
     * @param  \Laravie\QueryFilter\Field  $field
     */
    protected function getRelationSearchFilter(Field $field): Contracts\SearchFilter
    {
        [$relation, $column] = \explode('.', $field->getOriginalValue(), 2);

        return new Filters\RelationSearch($relation, $column);
    }
}
