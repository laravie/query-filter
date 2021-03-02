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

    /**
     * Wrap relation and field.
     */
    public function wrapRelationNameAndField(): array;

    /**
     * Split the given JSON selector into the field and the optional path and wrap them separately.
     */
    public function wrapJsonFieldAndPath(): array;
}
