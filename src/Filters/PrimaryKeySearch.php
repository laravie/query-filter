<?php

namespace Laravie\QueryFilter\Filters;

use Illuminate\Database\Eloquent\Model;
use Laravie\QueryFilter\Contracts\Filter\RequiresEloquent;
use Laravie\QueryFilter\Contracts\Keyword\AsExactValue;
use Laravie\QueryFilter\SearchFilter;

class PrimaryKeySearch extends SearchFilter implements AsExactValue, RequiresEloquent
{
    /**
     * Max primary key size.
     *
     * @var
     */
    protected $maxPrimaryKeySize = PHP_INT_MAX;

    /**
     * Construct new Primary Key Search.
     */
    public function __construct(?int $maxPrimaryKeySize = null)
    {
        $this->maxPrimaryKeySize = $maxPrimaryKeySize ?? $this->maxPrimaryKeySize;
    }

    /**
     * Apply primary key field search queries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($query, array $keywords, string $likeOperator, ?string $whereOperator = null)
    {
        if ($this->canSearchPrimaryKey($model = $query->getModel(), $search = head($keywords))) {
            $query->orWhere($model->getQualifiedKeyName(), $search);
        }

        return $query;
    }

    /**
     * Determine if can search primary key.
     *
     * @param  string|int  $search
     */
    protected function canSearchPrimaryKey(Model $model, $search): bool
    {
        return ctype_digit($search)
            && \in_array($model->getKeyType(), ['int', 'integer'])
            && ($model->getConnection()->getDriverName() != 'pgsql' || $search <= $this->maxPrimaryKeySize);
    }
}
