<?php

namespace Laravie\QueryFilter\Concerns;

trait ConditionallySearchingWildcard
{
    /**
     * Enable wildcard searching.
     *
     * @var bool|null
     */
    public $wildcardSearching = null;

    /**
     * Widlcard search variants.
     *
     * @var array<int, string>|null
     */
    public $wildcardSearchVariants = null;

    /**
     * Set wildcard search variants.
     *
     * @param  array<int, string>  $searchVariants
     *
     * @return $this
     */
    public function wildcardSearchVariants(?array $searchVariants)
    {
        $this->wildcardSearchVariants = $searchVariants;

        return $this;
    }

    /**
     * Set wildcard searching status.
     *
     * @return $this
     */
    public function wildcardSearching(?bool $wildcardSearching)
    {
        $this->wildcardSearching = $wildcardSearching;

        return $this;
    }

    /**
     * Enable using wildcard search.
     *
     * @return $this
     */
    public function allowWildcardSearching()
    {
        $this->wildcardSearching = true;

        return $this;
    }

    /**
     * Disable using wildcard search.
     *
     * @return $this
     */
    public function noWildcardSearching()
    {
        $this->wildcardSearching = false;

        return $this;
    }
}
