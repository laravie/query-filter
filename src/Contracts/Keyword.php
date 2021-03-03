<?php

namespace Laravie\QueryFilter\Contracts;

interface Keyword
{
    /**
     * Validate keyword value.
     */
    public function validate(): bool;

    /**
     * Get keyword value.
     */
    public function getValue(): string;

    /**
     * Get searchable strings.
     */
    public function all(): array;

    /**
     * Get searchable strings as lowercase.
     */
    public function allLowerCased(): array;
}
