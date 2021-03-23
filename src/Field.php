<?php

namespace Laravie\QueryFilter;

use Illuminate\Database\Query\Expression;
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
     * @param  static|\Illuminate\Database\Query\Expression|string  $name
     *
     * @return static
     */
    public static function make($name)
    {
        if ($name instanceof static) {
            return tap(new static($name), function ($field) use ($name) {
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
            [, $field] = $this->wrapRelationNameAndField();

            return static::make($field)->validate();
        } elseif ($this->isJsonPathSelector()) {
            return $this->validateJsonPath();
        }

        return parent::validate();
    }

    /**
     * Validate JSON field + path.
     */
    protected function validateJsonPath(): bool
    {
        $parts = \explode('->', $this->name, 2);

        $field = $parts[0];
        $path = \count($parts) > 1 ? $this->wrapJsonPath($parts[1], '->') : '';

        return (new Column($field))->validate() && (new Column(\str_replace('.', '', $path)))->validate();
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
            $column,
            'normal',
        ];
    }

    /**
     * Split the given JSON selector into the field and the optional path and wrap them separately.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    public function wrapJsonFieldAndPath($query): Expression
    {
        return new Expression(
            $query->getGrammar()->wrap($this->name)
        );
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
