<?php

namespace Laravie\QueryFilter\Contracts;

interface Field
{
    /**
     * Is relation selector.
     */
    public function isRelationSelector(): bool;

    /**
     * Is JSON path selector.
     */
    public function isJsonPathSelector(): bool;
}
