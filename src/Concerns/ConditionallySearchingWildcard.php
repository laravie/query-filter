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
