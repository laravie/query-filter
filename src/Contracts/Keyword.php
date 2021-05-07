<?php

namespace Laravie\QueryFilter\Contracts;

use Laravie\QueryFilter\Contracts\Filter\Filter;

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
     * Handle resolving keyword for filter.
     */
    public function handle(Filter $filter): array;
}
