# Release Notes for v2.x

This changelog references the relevant changes (bug and security fixes) done to `laravie/query-filter`.

## 2.1.0

Released: 2021-03-02

### Added

* Added ability to disable wildcard searching using `noWildcardSearching()` method (reverse of `allowWildcardSearching()`) from `Searchable`, `Taxanomy` and `Field` classes.
* Added ability to set wildcard character to be replaced using `wildcardCharacter()`, this can be disabled by setting it to `null`.

## 2.0.0

Released: 2020-09-07

### Changes

* Laravel Framework updated require minimum `8.0` and above.

### Deprecated

* Remove deprecated `Laravie\QueryFilter\Value\Keywords`.
