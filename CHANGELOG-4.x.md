# Release Notes for v4.x

This changelog references the relevant changes (bug and security fixes) done to `laravie/query-filter`.

## 4.0.0

Released: 2023-03-08

### Changes

* Laravel Framework updated require minimum `10.0` and above.
    - Support new `Illuminate\Contracts\Database\Query\Expression`
* Remove `Laravie\QueryFilter\Column::getOriginalValue()`, use `Laravie\QueryFilter\Column::getValue()` insteads.
* `Laravie\QueryFilter\Field` no longer implements `Stringable`.
