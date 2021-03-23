<?php

namespace Laravie\QueryFilter\Contracts;

use Illuminate\Database\Query\Expression;

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
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    public function wrapJsonFieldAndPath($query): Expression;
}
