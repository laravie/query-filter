<?php

namespace Laravie\QueryFilter;

use Orchestra\Support\Str;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class SearchQuery
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

    public function __contruct(?string $keyword, array $columns = [])
    {
        $this->keyword = $keyword;
        $this->columns = $columns;
    }

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
            $this->queryOnColumn($query, $column, $likeOperator);
        }

        return $query;
    }

    /**
     * Build wildcard query filter for field using where or orWhere.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @param  \Illuminate\Database\Query\Expression|string  $column
     * @param  string  $likeOperator
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function queryOnColumn($query, $column, string $likeOperator = 'like')
    {
        if ($column instanceof Expression) {
            return $this->queryOnColumnUsing($query, $column->getValue(), $likeOperator, 'orWhere');
        } elseif (! (Str::contains($column, '.') && $query instanceof EloquentBuilder)) {
            return $this->queryOnColumnUsing($query, $column, $likeOperator, 'orWhere');
        }

        $this->queryOnColumnUsing($query, $column, $likeOperator, 'orWhere');
        [$relation, $column] = \explode('.', $column, 2);

        return $query->orWhereHas($relation, function ($query) use ($column, $keyword) {
            $this->queryOnColumnUsing($query, $column, $likeOperator, 'where');
        });
    }

    /**
     * Build wildcard query filter for column using where or orWhere.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @param  string  $column
     * @param  array  $keyword
     * @param  string  $likeOperator
     * @param  string  $whereOperator
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function queryOnColumnUsing(
        $query,
        string $column,
        string $likeOperator,
        string $whereOperator = 'where'
    ) {
        $keywords = Str::searchable($this->keyword);

        return $query->{$whereOperator}(static function ($query) use ($column, $keywords, $likeOperator) {
            foreach ($keywords as $keyword) {
                $query->orWhere($column, $likeOperator, $keyword);
            }
        });
    }
}
