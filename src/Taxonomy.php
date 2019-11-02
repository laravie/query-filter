<?php

namespace Laravie\QueryFilter;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Taxonomy
{
    /**
     * Matches keyword.
     *
     * @var string
     */
    protected $keyword;

    /**
     * Matches columns.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * Matches rules.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Matches logic.
     *
     * @var \Laravie\QueryFilter\Value\Conditions
     */
    protected $conditions;

    /**
     * Construct a new Matches Query.
     *
     * @param  string|null  $keyword
     * @param  array  $rules
     * @param  array  $columns
     */
    public function __construct(?string $keyword, array $rules = [], array $columns = [])
    {
        $this->keyword = $keyword ?? '';
        $this->rules = $rules;
        $this->columns = $columns;

        $this->conditions = Value\Conditions::parse($keyword ?? '', \array_keys($rules));
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
     *
     * @return void
     */
    protected function matchBasicConditions($query): void
    {
        (new Searchable(
            $this->conditions->basic(), $this->columns
        ))->apply($query);
    }

    /**
     * Match tagged conditions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     *
     * @return void
     */
    protected function matchTaggedConditions($query): void
    {
        $tagged = $this->conditions->tagged();

        if (empty($tagged)) {
            return;
        }

        foreach ($this->rules as $keyword => $callback) {
            if (Str::contains($keyword, ':*') || Str::contains($keyword, ':[]')) {
                [$tag, $type] = \explode(':', $keyword, 2);

                $results = Arr::where($tagged, static function ($value) use ($tag) {
                    return Str::startsWith($value, "{$tag}:");
                });

                $query->unless(empty($results), static function ($query) use ($callback, $results, $type) {
                    if ($type === '*') {
                        [, $value] = \explode(':', $results[0] ?? null, 2);
                        $value = \trim($value, '"');
                    } else {
                        $value = \array_map(static function ($text) {
                            [, $value] = \explode(':', $text, 2);

                            return \trim($value, '"');
                        }, $results);
                    }

                    \call_user_func($callback, $query, $value);

                    return $query;
                });
            } else {
                $query->when(\in_array($keyword, $tagged), static function ($query) use ($callback) {
                    \call_user_func($callback, $query);

                    return $query;
                });
            }
        }
    }
}
