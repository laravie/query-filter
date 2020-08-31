# Release Notes for v1.x

This changelog references the relevant changes (bug and security fixes) done to `laravie/query-filter`.

## 1.8.0

Released: 2020-08-31

### Changes

* Allow to get search keyword using `Laravie\QueryFilter\Searchable::searchKeyword()`.

## 1.7.0

Released: 2020-07-25

### Changes

* Avoid ambigous column name when search with related fields using Eloquent. 

## 1.6.2

Released: 2020-06-19

### Fixes

* Avoid sanitizing unicode characters. [#4](https://github.com/laravie/query-filter/pull/4)

## 1.6.1

Released: 2020-02-16

### Added

* Added support for Laravel Framework `7.0+`.

## 1.6.0

Released: 2020-02-11

### Changes

* Sanitize againsts SQL wildcard attacks.

## 1.5.0

Released: 2019-12-30

### Added

* Added `Laravie\QueryFilter\Value\Terms` as replacement for deprecated `Laravie\QueryFilter\Value\Keywords`.
* Added documentation example how to add `whereLike` macros based on [@freekmurze](https://github.com/freekmurze)'s [Searching models using a where like query in Laravel](https://freek.dev/1182-searching-models-using-a-where-like-query-in-laravel)

### Deprecated

* Deprecate `Laravie\QueryFilter\Value\Keywords`.

## 1.4.0

Released: 2019-12-17

### Changes

* Refactor `Laravie\QueryFilter\Taxanomy` and `Laravie\QueryFilter\Value\Keywords`.

## 1.3.0

Released: 2019-12-15

### Changes

* Taxanomy should skipped if tagged values is equivalent to empty string.

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
