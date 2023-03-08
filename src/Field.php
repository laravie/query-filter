<?php

namespace Laravie\QueryFilter;

use Laravie\QueryFilter\Concerns\ConditionallySearchingWildcard;
use Laravie\QueryFilter\Contracts\Field as FieldContract;

class Field extends Column implements FieldContract
{
    use ConditionallySearchingWildcard;

    /**
     * Searchable instance.
     *
     * @var \Laravie\QueryFilter\Searchable
     */
    protected $searchable;

    /**
     * Make a new Field value object.
     *
     * @param  static|\Illuminate\Contracts\Database\Query\Expression|string  $name
     * @return static
     */
    public static function make($name)
    {
        if ($name instanceof static) {
            return tap(new static($name->getValue()), static function ($field) use ($name) {
                $field->wildcardSearching = $name->wildcardSearching;
            });
        }

        return new static($name);
    }

    /**
     * Validate column.
     */
    public function validate(): bool
    {
        if ($this->isRelationSelector()) {
            return $this->validateRelationColumn();
        } elseif ($this->isJsonPathSelector()) {
            return $this->validateJsonPath();
        }

        return parent::validate();
    }

    /**
     * Validate Relation  field + path.
     */
    protected function validateRelationColumn(): bool
    {
        if ($this->isExpression()) {
            return false;
        }

        /** @var string $name */
        $name = $this->name;

        [, $field] = explode('.', $name, 2);

        return Column::make($field)->validate();
    }

    /**
     * Validate JSON column + path.
     */
    protected function validateJsonPath(): bool
    {
        if ($this->isExpression()) {
            return false;
        }

        /** @var string $name */
        $name = $this->name;

        $parts = explode('->', $name, 2);

        $field = $parts[0];
        $path = \count($parts) > 1 ? $this->wrapJsonPath($parts[1], '->') : '';

        return Column::make($field)->validate() && Column::make(str_replace('.', '', $path))->validate();
    }

    /**
     * Is relation selector.
     */
    public function isRelationSelector(): bool
    {
        if ($this->isExpression()) {
            return false;
        }

        /** @var string $name */
        $name = $this->name;

        return strpos($name, '.') !== false;
    }

    /**
     * Is JSON path selector.
     */
    public function isJsonPathSelector(): bool
    {
        if ($this->isExpression()) {
            return false;
        }

        /** @var string $name */
        $name = $this->name;

        return strpos($name, '->') !== false;
    }

    /**
     * Wrap the given JSON path.
     */
    protected function wrapJsonPath(string $value, string $delimiter = '->'): string
    {
        /** @var string $value */
        $value = preg_replace("/([\\\\]+)?\\'/", "\\'", $value);

        return str_replace($delimiter, '.', $value);
    }
}
