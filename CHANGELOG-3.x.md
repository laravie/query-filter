# Release Notes for v3.x

This changelog references the relevant changes (bug and security fixes) done to `laravie/query-filter`.

## 3.3.0

Released: 2022-03-13

### Changes

* Added support for Laravel Framework 9.

## 3.2.1

Released: 2021-12-12

### Changes

* Improves generic types docblock.

## 3.2.0

Released: 2021-10-27

### Added

* Added support for PHP 8.1.

## 3.1.0

Released: 2021-06-18

### Added

* Added configurable `wildcardSearchVariants()`.

```php
$searchable = (new Searchable(
    'Administrator', ['name', 'roles.name']
))->wildcardSearchVariants(['%{keyword}%']); 

return $searchable->apply($query)->get(); 
```

## 3.0.0

Released: 2021-05-12

### Added

* Added `Laravie\QueryFilter\Filters\PrimaryKeySearch` and `Laravie\QueryFilter\Filters\MorphRelationSearch`.

### Changes

* Moved field, JSON and Relation search to classes:
    - `Laravie\QueryFilter\Filters\FieldSearch`
    - `Laravie\QueryFilter\Filters\JsonFieldSearch`
    - `Laravie\QueryFilter\Filters\RelationSearch`
* Utilise JSON selector parser from `illuminate/database`.

### Breaking Changes

* Moved `Laravie\QueryFilter\Value\Field` to `Laravie\QueryFilter\Field`.
* Moved `Laravie\QueryFilter\Value\Keyword` to `Laravie\QueryFilter\Keyword`.
