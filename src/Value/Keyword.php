<?php

namespace Laravie\QueryFilter\Value;

use Illuminate\Support\Str;

class Keyword
{
    /**
     * Keyword value.
     *
     * @var string
     */
    protected $value;

    /**
     * Construct a new Keyword value object.
     *
     * @param  string  $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Validate keyword value.
     *
     * @return bool
     */
    public function validate(): bool
    {
        return ! empty($this->value);
    }

    /**
     * Get searchable strings as lowercased.
     *
     * @param  string $wildcard
     * @param  string $replacement
     * @return array
     */
    public function allLowerCased(string $wildcard = '*', string $replacement = '%'): array
    {
        return static::searchable(Str::lower($this->value), $wildcard, $replacement);
    }

    /**
     * Get searchable strings.
     *
     * @param  string $wildcard
     * @param  string $replacement
     * @return array
     */
    public function all(string $wildcard = '*', string $replacement = '%'): array
    {
        return static::searchable($this->value, $wildcard, $replacement);
    }

    /**
     * Convert basic string to searchable result.
     *
     * @param  string  $text
     * @param  string  $wildcard
     * @param  string  $replacement
     *
     * @return array
     */
    public static function searchable(string $text, string $wildcard = '*', string $replacement = '%'): array
    {
        if (! Str::contains($text, [$wildcard, $replacement])) {
            return [
                "{$text}", "{$text}%", "%{$text}", "%{$text}%",
            ];
        }

        return [
            \str_replace($wildcard, $replacement, $text),
        ];
    }
}
