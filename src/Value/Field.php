<?php

namespace Laravie\QueryFilter\Value;

use Illuminate\Support\Str;
use Illuminate\Database\Query\Expression;

class Field
{
    /**
     * Field name.
     *
     * @var \Illuminate\Database\Query\Expression|string
     */
    protected $name;

    /**
     * Construct a new Field value object.
     *
     * @param  \Illuminate\Database\Query\Expression|string  $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Is an instance of Illuminate\Database\Query\Expression.
     *
     * @return bool
     */
    public function isExpression(): bool
    {
        return $this->name instanceof Expression;
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
     * Get expression value.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->isExpression() ? $this->name->getValue() : $this->name;
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
