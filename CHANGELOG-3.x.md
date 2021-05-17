# Release Notes for v3.x

This changelog references the relevant changes (bug and security fixes) done to `laravie/query-filter`.

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
