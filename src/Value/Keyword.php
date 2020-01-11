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
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Validate keyword value.
     */
    public function validate(): bool
    {
        return ! empty($this->value);
    }

    /**
     * Get searchable strings as lowercased.
     */
    public function allLowerCased(string $wildcard = '*', string $replacement = '%'): array
    {
        return static::searchable(Str::lower($this->value), $wildcard, $replacement);
    }

    /**
     * Get searchable strings.
     */
    public function all(string $wildcard = '*', string $replacement = '%'): array
    {
        return static::searchable($this->value, $wildcard, $replacement);
    }

    /**
     * Convert basic string to searchable result.
     */
    public static function searchable(string $text, string $wildcard = '*', string $replacement = '%'): array
    {
        $text = static::sanitize($text);

        if (empty($text)) {
            return [];
        } elseif (! Str::contains($text, [$wildcard, $replacement])) {
            return [
                "{$text}", "{$text}%", "%{$text}", "%{$text}%",
            ];
        }

        return [
            \str_replace($wildcard, $replacement, $text),
        ];
    }

    /**
     * Sanitize keywords.
     */
    public static function sanitize(string $keyword): string
    {
        $words = \preg_replace('/[^\w\*\s]/i', '', $keyword);

        if (empty(\trim($words))) {
            return '';
        } elseif (\strlen($words) > 3 && \strlen($words) < (\strlen($keyword) * 0.5)) {
            return $words;
        }

        return $keyword;
    }
}
