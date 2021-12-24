<?php

namespace Laravie\QueryFilter;

use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Tappable;

class Searchable
{
    use Concerns\ConditionallySearchingWildcard,
        Concerns\SearchingWildcard,
        Tappable;

    /**
     * Search keyword.
     *
     * @var string
     */
    protected $keyword;

    /**
     * Search columns.
     *
     * @var array<int, string|\Laravie\QueryFilter\Contracts\Filter\Filter>
     */
    protected $fields = [];

    /**
     * Construct a new Search Query.
     *
     * @param  array<int, string|\Laravie\QueryFilter\Contracts\Filter\Filter>  $fields
     */
    public function __construct(?string $keyword, array $fields = [])
    {
        $this->keyword = $keyword ?? '';
        $this->fields = array_filter($fields);
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
     * @param  \Illuminate\Contracts\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     *
     * @return \Illuminate\Contracts\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function apply($query)
    {
        $keywords = $this->searchKeyword();

        if (! $keywords->validate() || empty($this->fields)) {
            return $query;
        }

        $keywords->wildcardCharacter($this->wildcardCharacter)
            ->wildcardReplacement($this->wildcardReplacement)
            ->wildcardSearchVariants($this->wildcardSearchVariants)
            ->wildcardSearching($this->wildcardSearching ?? true);

        $likeOperator = like_operator(connection_type($query));

        /**
         * @var \Illuminate\Support\Collection<int, \Laravie\QueryFilter\Contracts\Filter\Filter> $filters
         * @var \Illuminate\Support\Collection<int, string> $fields
         */
        [$filters, $fields] = Collection::make($this->fields)->partition(static function ($field) {
            return $field instanceof Contracts\Filter\Filter;
        });

        $query->where(function ($query) use ($fields, $filters, $keywords, $likeOperator) {
            foreach ($filters as $filter) {
                $filter->validate($query)->apply(
                    $query, $keywords->handle($filter), $likeOperator, 'orWhere'
                );
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
     * @param  \Illuminate\Contracts\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     *
     * @return \Illuminate\Contracts\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
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
     * @param  \Illuminate\Contracts\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     *
     * @return \Illuminate\Contracts\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
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

        tap($this->getFieldSearchFilter($field), function ($filter) use ($field, $query, $likeOperator, $whereOperator) {
            $filter->validate($query)->apply(
                $query,
                $this->searchKeyword()
                    ->wildcardCharacter($this->wildcardCharacter)
                    ->wildcardReplacement($this->wildcardReplacement)
                    ->wildcardSearchVariants($this->wildcardSearchVariants)
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
     * @param  \Illuminate\Contracts\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     *
     * @return \Illuminate\Contracts\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function queryOnJsonColumnUsing(
        $query,
        Field $field,
        string $likeOperator,
        string $whereOperator = 'where'
    ) {
        tap($this->getJsonFieldSearchFilter($field), function ($filter) use ($field, $query, $likeOperator, $whereOperator) {
            $filter->validate($query)->apply(
                $query,
                $this->searchKeyword()
                    ->wildcardCharacter($this->wildcardCharacter)
                    ->wildcardReplacement($this->wildcardReplacement)
                    ->wildcardSearchVariants($this->wildcardSearchVariants)
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
        tap($this->getRelationSearchFilter($field), function ($filter) use ($field, $query, $likeOperator) {
            $filter->validate($query)->apply(
                $query,
                $this->searchKeyword()
                    ->wildcardCharacter($this->wildcardCharacter)
                    ->wildcardReplacement($this->wildcardReplacement)
                    ->wildcardSearchVariants($this->wildcardSearchVariants)
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
    protected function getFieldSearchFilter(Field $field): Contracts\Filter\Filter
    {
        return new Filters\FieldSearch($field->getOriginalValue());
    }

    /**
     * Get JSON Field Search Filter.
     *
     * @param  \Laravie\QueryFilter\Field  $field
     */
    protected function getJsonFieldSearchFilter(Field $field): Contracts\Filter\Filter
    {
        return new Filters\JsonFieldSearch($field->getOriginalValue());
    }

    /**
     * Get Relation Search Filter.
     *
     * @param  \Laravie\QueryFilter\Field  $field
     */
    protected function getRelationSearchFilter(Field $field): Contracts\Filter\Filter
    {
        [$relation, $column] = explode('.', $field->getOriginalValue(), 2);

        return new Filters\RelationSearch($relation, $column);
    }
}
