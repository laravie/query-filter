<?php

namespace Laravie\QueryFilter;

use Orchestra\Support\Str;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class Searchable
{
    /**
     * Search keyword.
     *
     * @var string
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
     *
     * @param  string|null  $keyword
     * @param  array  $columns
     */
    public function __construct(?string $keyword, array $columns = [])
    {
        $this->keyword = $keyword ?? '';
        $this->columns = $columns;
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
        if (empty($this->keyword) || empty($this->columns)) {
            return $query;
        }

        $connectionType = $query instanceof EloquentBuilder
            ? $query->getModel()->getConnection()->getDriverName()
            : $query->getConnection()->getDriverName();

        $likeOperator = $connectionType == 'pgsql' ? 'ilike' : 'like';

        foreach ($this->columns as $column) {
            $this->queryOnColumn($query, new Value\Field($column), $likeOperator);
        }

        return $query;
    }

    /**
     * Build wildcard query filter for field using where or orWhere.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @param  \Laravie\QueryFilter\Value\Field  $column
     * @param  string  $likeOperator
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function queryOnColumn($query, Value\Field $column, string $likeOperator = 'like')
    {
        if ($column->isExpression()) {
            return $this->queryOnColumnUsing($query, new Value\Field($column->getValue()), $likeOperator, 'orWhere');
        } elseif ($column->isRelationSelector() && $query instanceof EloquentBuilder) {
            [$relation, $column] = $column->wrapRelationNameAndField();

            return $query->orWhereHas($relation, function ($query) use ($column, $keyword) {
                $this->queryOnColumnUsing($query, $column, $likeOperator, 'where');
            });
        }

        return $this->queryOnColumnUsing($query, $column, $likeOperator, 'orWhere');
    }

    /**
     * Build wildcard query filter for column using where or orWhere.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @param  \Laravie\QueryFilter\Value\Field  $column
     * @param  string  $likeOperator
     * @param  string  $whereOperator
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function queryOnColumnUsing(
        $query,
        Value\Field $column,
        string $likeOperator,
        string $whereOperator = 'where'
    ) {
        if ($column->isJsonPathSelector()) {
            return $this->queryOnJsonColumnUsing($query, $column, $likeOperator, $whereOperator);
        }

        $keywords = Str::searchable($this->keyword);

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
     * @param  \Laravie\QueryFilter\Value\Field  $column
     * @param  string  $likeOperator
     * @param  string  $whereOperator
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function queryOnJsonColumnUsing(
        $query,
        Value\Field $column,
        string $likeOperator,
        string $whereOperator = 'where'
    ) {
        $keywords = Str::searchable(Str::lower($this->keyword));

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
