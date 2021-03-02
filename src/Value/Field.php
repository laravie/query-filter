<?php

namespace Laravie\QueryFilter\Value;

use Laravie\QueryFilter\Concerns\ConditionallySearchingWildcard;

class Field extends Column
{
    use ConditionallySearchingWildcard;

    /**
     * Validate column.
     */
    public function validate(): bool
    {
        if ($this->isRelationSelector()) {
            [, $field] = $this->wrapRelationNameAndField();

            return $field->validate();
        } elseif ($this->isJsonPathSelector()) {
            [, $path] = $this->wrapJsonFieldAndPath();

            return (new Column(\str_replace('.', '', $path)))->validate();
        }

        return parent::validate();
    }

    /**
     * Is relation selector.
     */
    public function isRelationSelector(): bool
    {
        return \strpos($this->name, '.') !== false;
    }

    /**
     * Is JSON path selector.
     */
    public function isJsonPathSelector(): bool
    {
        return \strpos($this->name, '->') !== false;
    }

    /**
     * Wrap relation and field.
     */
    public function wrapRelationNameAndField(): array
    {
        [$relation, $column] = \explode('.', $this->name, 2);

        return [
            $relation,
            new static($column),
            'normal',
        ];
    }

    /**
     * Split the given JSON selector into the field and the optional path and wrap them separately.
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

        return \str_replace($delimiter, '.', $value);
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
