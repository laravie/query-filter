<?php

namespace Laravie\QueryFilter\Value;

use Illuminate\Support\Str;
use Laravie\QueryFilter\Taxonomy;

class Keyword
{
    /**
     * Keyword value.
     *
     * @var string
     */
    protected $value;

    /**
     * Enable wildcard searching.
     *
     * @var bool
     */
    protected $enableWildcardSearching = true;

    /**
     * Construct a new Keyword value object.
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Enable using wildcard search.
     *
     * @return $this
     */
    public function withSearchingWildcard()
    {
        $this->enableWildcardSearching = true;

        return $this;
    }

    /**
     * Disable using wildcard search.
     *
     * @return $this
     */
    public function withoutSearchingWildcard()
    {
        $this->enableWildcardSearching = false;

        return $this;
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
        return static::searchable($this->value, $wildcard, $replacement, $this->enableWildcardSearching);
    }

    /**
     * Convert basic string to searchable result.
     */
    public static function searchable(string $text, string $wildcard = '*', string $replacement = '%', bool $enableWildcardSearching = true): array
    {
        $text = static::sanitize($text);

        if (empty($text)) {
            return [];
        } elseif (! Str::contains($text, [$wildcard, $replacement]) && $enableWildcardSearching === true) {
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
        $words = \preg_replace('/[^\w\*\s]/iu', '', $keyword);

        if (empty(\trim($words))) {
            return '';
        } elseif (\strlen($words) > 3 && \strlen($words) < (\strlen($keyword) * 0.5)) {
            return $words;
        }

        return $keyword;
    }
}
