<?php

namespace Laravie\QueryFilter;

use Illuminate\Support\Traits\Tappable;

class Taxonomy
{
    use Concerns\ConditionallySearchingWildcard,
        Concerns\SearchingWildcard,
        Tappable;

    /**
     * Taxonomy columns.
     *
     * @var array<int, string|\Laravie\QueryFilter\Contracts\Filter\Filter>
     */
    protected $fields = [];

    /**
     * Taxonomy rules.
     *
     * @var array<string, \Closure|callable>
     */
    protected $rules = [];

    /**
     * Taxonomy keywords.
     *
     * @var \Laravie\QueryFilter\Terms
     */
    protected $terms;

    /**
     * Construct a new Matches Query.
     *
     * @param  array<string, \Closure|callable>  $rules
     * @param  array<int, string|\Laravie\QueryFilter\Contracts\Filter\Filter>  $fields
     */
    public function __construct(?string $terms, array $rules = [], array $fields = [])
    {
        $this->rules = array_filter($rules);
        $this->fields = $fields;

        $this->terms = Terms::parse($terms ?? '', array_keys($this->rules));
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
        $this->matchTaggedConditions($query);
        $this->matchBasicConditions($query);

        return $query;
    }

    /**
     * Match basic conditions.
     *
     * @param  \Illuminate\Contracts\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    protected function matchBasicConditions($query): void
    {
        $searchable = (new Searchable($this->terms->basic(), $this->fields))
                            ->wildcardCharacter($this->wildcardCharacter);

        if (($this->wildcardSearching ?? true) === true) {
            $searchable->allowWildcardSearching();
        } else {
            $searchable->noWildcardSearching();
        }

        $searchable->apply($query);
    }

    /**
     * Match tagged conditions.
     *
     * @param  \Illuminate\Contracts\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    protected function matchTaggedConditions($query): void
    {
        if (\count($this->terms) < 1) {
            return;
        }

        foreach ($this->rules as $term => $callback) {
            if (strpos($term, ':*') !== false || strpos($term, ':[]') !== false) {
                $value = $this->terms->where($term);

                $query->unless(empty($value), static function ($query) use ($callback, $value) {
                    \call_user_func($callback, $query, $value);

                    return $query;
                });
            } else {
                $query->when($this->terms->is($term), static function ($query) use ($callback) {
                    \call_user_func($callback, $query);

                    return $query;
                });
            }
        }
    }
}
