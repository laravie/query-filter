<?php

namespace Laravie\QueryFilter\Concerns;

trait SearchingWildcard
{
    /**
     * Wildcard search character.
     *
     * @var string|null
     */
    public $wildcardCharacter = '*';

    /**
     * Wildcard search character replacement.
     *
     * @var string
     */
    public $wildcardReplacement = '%';

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
}
