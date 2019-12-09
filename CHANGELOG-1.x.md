# Release Notes for v1.x

This changelog references the relevant changes (bug and security fixes) done to `laravie/query-filter`.

## 1.2.0

Released: 2019-12-09

### Changes

* Validate relationship field and JSON path to not include invalid column name structure.

## 1.1.0

Released: 2019-12-09

### Changes

* Use `strpos()` instead of `Str::contains()` when possible.

## 1.0.2

Released: 2019-11-03

### Fixes

* Fixes generated search keywords when it only contains either `%` or `*`.

## 1.0.1

Released: 2019-11-03

### Changes

* Allow to be used with `laravel/framework` `5.8.+`.

## 1.0.0

Released: 2019-11-03

Initial stable release.

* `Laravie\QueryFilter\Orderable` from `orchestra/support-core`.
* `Laravie\QueryFilter\Searchable` from `orchestra/support-core`.
* `Laravie\QueryFilter\Taxonomy` from `orchestra/model`.
