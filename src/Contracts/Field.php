<?php

namespace Laravie\QueryFilter\Contracts;

interface Field
{
    /**
     * Validate column.
     */
    public function validate(): bool;

    /**
     * Is relation selector.
     */
    public function isRelationSelector(): bool;

    /**
     * Is JSON path selector.
     */
    public function isJsonPathSelector(): bool;
}
