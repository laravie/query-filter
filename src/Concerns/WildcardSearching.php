<?php

namespace Laravie\QueryFilter\Concerns;

trait WildcardSearching
{
    /**
     * Wildcard search character.
     *
     * @var string|null
     */
    protected $wildcardCharacter = '*';

    /**
     * Wildcard search character replacement.
     *
     * @var string
     */
    protected $wildcardReplacement = '%';

    /**
     * Enable wildcard searching.
     *
     * @var bool
     */
    protected $wildcardSearching = true;

    /**
     * Set wildcard search character.
     *
     * @return $this
     */
    public function wildcardCharacter(?string $character = null)
    {
        $this->wildcardCharacter = $character;

        return $this;
    }

    /**
     * Set wildcard search replacement.
     *
     * @return $this
     */
    public function wildcardReplacement(?string $character = null)
    {
        $this->wildcardReplacement = $character;

        return $this;
    }

    /**
     * Enable using wildcard search.
     *
     * @return $this
     */
    public function withWildcardSearching()
    {
        $this->wildcardSearching = true;

        return $this;
    }

    /**
     * Disable using wildcard search.
     *
     * @return $this
     */
    public function withoutWildcardSearching()
    {
        $this->wildcardSearching = false;

        return $this;
    }
}
