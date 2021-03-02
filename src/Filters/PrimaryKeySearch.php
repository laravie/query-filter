<?php

namespace Laravie\QueryFilter\Filters;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Laravie\QueryFilter\Contracts\Keyword;
use Laravie\QueryFilter\SearchFilter;

class PrimaryKeySearch extends SearchFilter
{
    /**
     * Max primary key size.
     *
     * @var
     */
    protected $maxPrimaryKeySize = PHP_INT_MAX;

    /**
     * List of columns.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * Construct new Primary Key Search.
     *
     * @param  int  $maxPrimaryKeySize
     * @param  array  $columns
     */
    public function __construct(int $maxPrimaryKeySize, array $columns)
    {
        $this->maxPrimaryKeySize = $maxPrimaryKeySize;
        $this->columns = $columns;
    }

    /**
     * Apply primary key field search queries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($query, Keyword $keywords, string $likeOperator, ?string $whereOperator = null)
    {
        if (!$query instanceof EloquentBuilder) {
            throw new RuntimeException('Unable to use PrimaryKeySearch with [' . get_class($query) . ']');
        }

        if ($this->canSearchPrimaryKey($model = $query->getModel(), $search = $keywords->getValue())) {
            $query->orWhere($model->getQualifiedKeyName(), $search);
        }

        return $query;
    }

    /**
     * Determine if can search primary key.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string|int  $search
     *
     * @return bool
     */
    protected function canSearchPrimaryKey(Model $model, $search): bool
    {
        return \ctype_digit($search)
            && \in_array($model->getKeyType(), ['int', 'integer'])
            && ($model->getConnection()->getDriverName() != 'pgsql' || $search <= $this->maxPrimaryKeySize)
            && in_array($model->getKeyName(), $this->columns);
    }
}
