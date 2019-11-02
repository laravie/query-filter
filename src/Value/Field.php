<?php

namespace Laravie\QueryFilter\Value;

use Illuminate\Support\Str;

class Field extends Column
{
    /**
     * Validate column.
     *
     * @return bool
     */
    public function validate(): bool
    {
        if ($this->isRelationSelector() || $this->isJsonPathSelector()) {
            return true;
        }

        return parent::validate();
    }

    /**
     * Is relation selector.
     *
     * @return bool
     */
    public function isRelationSelector(): bool
    {
        return Str::contains($this->name, '.');
    }

    /**
     * Is JSON path selector.
     *
     * @return bool
     */
    public function isJsonPathSelector(): bool
    {
        return Str::contains($this->name, '->');
    }

    /**
     * Wrap relation and field.
     *
     * @return array
     */
    public function wrapRelationNameAndField(): array
    {
        [$relation, $column] = \explode('.', $this->name, 2);

        return [
            $relation,
            new static($column),
        ];
    }

    /**
     * Split the given JSON selector into the field and the optional path and wrap them separately.
     *
     * @return array
     */
    public function wrapJsonFieldAndPath(): array
    {
        $parts = \explode('->', $this->name, 2);
        $field = $parts[0];
        $path = \count($parts) > 1 ? $this->wrapJsonPath($parts[1], '->') : '';

        return [$field, $path];
    }

    /**
     * Wrap the given JSON path.
     *
     * @param  string  $value
     * @param  string  $delimiter
     *
     * @return string
     */
    public function wrapJsonPath($value, $delimiter = '->')
    {
        $value = \preg_replace("/([\\\\]+)?\\'/", "\\'", $value);

        return \str_replace($delimiter, '"."', $value);
    }

    /**
     * Convert to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getValue();
    }
}
