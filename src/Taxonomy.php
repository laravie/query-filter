<?php

namespace Laravie\QueryFilter;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Taxonomy
{
    /**
     * Taxonomy columns.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * Taxonomy rules.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Taxonomy keywords.
     *
     * @var \Laravie\QueryFilter\Value\Keywords
     */
    protected $keywords;

    /**
     * Construct a new Matches Query.
     */
    public function __construct(?string $keyword, array $rules = [], array $columns = [])
    {
        $this->rules = \array_filter($rules);
        $this->columns = $columns;

        $this->keywords = Value\Keywords::parse($keyword ?? '', \array_keys($this->rules));
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
        $this->matchTaggedConditions($query);
        $this->matchBasicConditions($query);

        return $query;
    }

    /**
     * Match basic conditions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    protected function matchBasicConditions($query): void
    {
        (new Searchable(
            $this->keywords->basic(), $this->columns
        ))->apply($query);
    }

    /**
     * Match tagged conditions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    protected function matchTaggedConditions($query): void
    {
        if (\count($this->keywords) > 0) {
            return;
        }

        foreach ($this->rules as $keyword => $callback) {
            if (\strpos($keyword, ':*') !== false || \strpos($keyword, ':[]') !== false) {
                $value = $this->keywords->where($keyword);

                $query->unless(empty($value), static function ($query) use ($callback, $value) {
                    \call_user_func($callback, $query, $value);

                    return $query;
                });
            } else {
                $query->when($this->keyword->is($keyword), static function ($query) use ($callback) {
                    \call_user_func($callback, $query);

                    return $query;
                });
            }
        }
    }
}
