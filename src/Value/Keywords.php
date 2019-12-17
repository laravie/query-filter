<?php

namespace Laravie\QueryFilter\Value;

use Illuminate\Support\Str;

class Keywords
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
    public static function parse(string $keyword, array $rules)
    {
        $basic = [];
        $tagged = [];

        $tags = \array_map(static function ($value) {
            [$tag, ] = \explode(':', $value, 2);

            return "{$tag}:";
        }, $rules);

        if (\preg_match_all('/([\w]+:\"[\w\s]*\"|[\w]+:[\w\S]+|[\w\S]+)\s?/', $keyword, $keywords)) {
            foreach ($keywords[1] as $index => $keyword) {
                if (! Str::startsWith($keyword, $tags)) {
                    \array_push($basic, $keyword);
                } else {
                    \array_push($tagged, $keyword);
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
     * Keywords contains basic.
     */
    public function hasBasic(): bool
    {
        return ! empty($this->basic);
    }

    /**
     * Keywords contains tagged.
     */
    public function hasTagged(): bool
    {
        return ! empty($this->tagged);
    }
}
