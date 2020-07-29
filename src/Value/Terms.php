<?php

namespace Laravie\QueryFilter\Value;

use Countable;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class Terms implements Countable
{
    /**
     * Basic condition.
     *
     * @var string
     */
    protected $basic = '';

    /**
     * Tagged conditions.
     *
     * @var array
     */
    protected $tagged = [];

    /**
     * Construct a new Condition value object.
     */
    public function __construct(string $basic, array $tagged)
    {
        $this->basic = $basic;
        $this->tagged = $tagged;
    }

    /**
     * Parse rules from keyword.
     *
     * @return static
     */
    public static function parse(string $term, array $rules)
    {
        $basic = [];
        $tagged = [];

        $tags = Collection::make($rules)
            ->map(static function ($value) {
                [$tag, ] = \explode(':', $value, 2);

                return "{$tag}:";
            })->all();

        if (\preg_match_all('/([\w]+:\"[\w\s]*\"|[\w]+:[\w\S]+|[\w\S]+)\s?/', $term, $terms)) {
            foreach ($terms[1] as $index => $term) {
                if (! Str::startsWith($term, $tags)) {
                    \array_push($basic, $term);
                } else {
                    \array_push($tagged, $term);
                }
            }
        }

        return new static(
            \implode(' ', $basic),
            $tagged
        );
    }

    /**
     * Get basic conditions.
     */
    public function basic(): string
    {
        return $this->basic;
    }

    /**
     * Get tagged conditions.
     */
    public function tagged(): array
    {
        return $this->tagged;
    }

    /**
     * Count tagged keywords.
     */
    public function count(): int
    {
        return \count($this->tagged);
    }

    /**
     * Filter same as tagged.
     */
    public function is(string $term): bool
    {
        return \in_array($term, $this->tagged());
    }

    /**
     * Filter tagged by keyword.
     *
     * @return string|array|null
     */
    public function where(string $term)
    {
        [$tag, $type] = \explode(':', $term, 2);

        $results = Collection::make($this->tagged())
            ->filter(static function ($value) use ($tag) {
                return Str::startsWith($value, "{$tag}:");
            })->values();

        if ($results->isEmpty()) {
            return [];
        }

        if ($type === '*') {
            [, $value] = \explode(':', $results[0] ?? null, 2);

            return \trim($value, '"');
        }

        return $results->map(static function ($text) {
            [, $value] = \explode(':', $text, 2);

            return \trim($value, '"');
        })->filter(static function ($text) {
            return ! empty($text);
        })->values()->all();
    }
}
