<?php

namespace Laravie\QueryFilter;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class MatchQuery
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
     * @var array
     */
    protected $conditions = [
        'basic' => '',
        'tagged' => []
    ];

    /**
     * Construct a new Matches Query.
     *
     * @param string  $keyword
     * @param array  $rules
     * @param array  $columns
     */
    public function __construct(?string $keyword, array $rules = [], array $columns = [])
    {
        $this->keyword = $keyword;
        $this->rules = $rules;
        $this->columns = $columns;

        [$basic, $tagged] = $this->parseRulesFromKeyword($keyword ?? '', $rules);

        $this->conditions = \compact('basic', 'tagged');
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
        (new SearchQuery(
            $this->conditions['basic'], $this->columns
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
        if (empty($this->conditions['advanced'])) {
            return;
        }

        foreach ($this->rules as $keyword => $callback) {
            if (Str::contains($keyword, ':*') || Str::contains($keyword, ':[]')) {
                [$tag, $type] = \explode(':', $keyword, 2);

                $results = Arr::where($this->conditions['tagged'], static function ($value) use ($tag) {
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
                $query->when(\in_array($keyword, $this->conditions['tagged']), static function ($query) use ($callback) {
                    \call_user_func($callback, $query);

                    return $query;
                });
            }
        }
    }

    /**
     * Parse rules from keyword.
     *
     * @param  string  $keyword
     * @param  array  $rules
     * @return array
     */
    protected function parseRulesFromKeyword(string $keyword, array $rules): array
    {
        $basic = [];
        $tagged = [];

        $tags = \array_map(static function ($value) {
            [$tag, ] = \explode(':', $value, 2);

            return "{$tag}:";
        }, \array_keys($rules));

        if (\preg_match_all('/([\w]+:\"[\w\s]*\"|[\w]+:[\w\S]+|[\w\S]+)\s?/', $keyword, $keywords)) {
            foreach ($keywords[1] as $index => $keyword) {
                if (! Str::startsWith($keyword, $tags)) {
                    \array_push($basic, $keyword);
                } else {
                    \array_push($tagged, $keyword);
                }
            }
        }

        return [
            \implode(' ', $basic),
            $tagged,
        ];
    }
}
