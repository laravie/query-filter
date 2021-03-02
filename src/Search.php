<?php

namespace Laravie\QueryFilter;

abstract class Search implements Contracts\Search
{
    /**
     * Field implementation.
     *
     * @var \Laravie\QueryFilter\Contracts\Field
     */
    protected $field;

    /**
     * Set field for current search.
     *
     * @return $this
     */
    public function field(Contracts\Field $field)
    {
        $this->field = $field;

        return $this;
    }
}
