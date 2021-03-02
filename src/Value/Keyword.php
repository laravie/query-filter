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
     * List default search variations.
     *
     * @var string[]
     */
    public static $defaultSearchVariations = ['{keyword}', '{keyword}%', '%{keyword}', '%{keyword}%'];

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
     * Get searchable strings as lowercase.
     */
    public function allLowerCased(
        ?string $wildcard = '*',
        ?string $replacement = '%',
        bool $wildcardSearching = true
    ): array {
        return static::searchable(Str::lower($this->value), $wildcard, $replacement, $wildcardSearching);
    }

    /**
     * Get searchable strings.
     */
    public function all(
        ?string $wildcard = '*',
        ?string $replacement = '%',
        bool $wildcardSearching = true
    ): array {
        return static::searchable($this->value, $wildcard, $replacement, $wildcardSearching);
    }

    /**
     * Convert basic string to searchable result.
     */
    public static function searchable(string $text, ?string $wildcard = '*', ?string $replacement = '%', bool $wildcardSearching = true): array
    {
        $text = static::sanitize($text);

        if (empty($text)) {
            return [];
        } elseif (\is_null($wildcard) || \is_null($replacement)) {
            return [$text];
        } elseif (! Str::contains($text, [$wildcard, $replacement]) && $wildcardSearching === true) {
            return collect(static::$defaultSearchVariations)
                ->map(function ($string) use ($text) {
                    return Str::replaceFirst('{keyword}', $text, $string);
                })->all();
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
