
Database/Eloquent Query Builder filters for Laravel
==============

[![Build Status](https://travis-ci.org/laravie/query-filter.svg?branch=master)](https://travis-ci.org/laravie/query-filter)
[![Latest Stable Version](https://poser.pugx.org/laravie/query-filter/v/stable)](https://packagist.org/packages/laravie/query-filter)
[![Total Downloads](https://poser.pugx.org/laravie/query-filter/downloads)](https://packagist.org/packages/laravie/query-filter)
[![Latest Unstable Version](https://poser.pugx.org/laravie/query-filter/v/unstable)](https://packagist.org/packages/laravie/query-filter)
[![License](https://poser.pugx.org/laravie/query-filter/license)](https://packagist.org/packages/laravie/query-filter)
[![Coverage Status](https://coveralls.io/repos/github/laravie/query-filter/badge.svg?branch=master)](https://coveralls.io/github/laravie/query-filter?branch=master)

* [Installation](#installation)
    - [Quick Installation](#quick-installation)
* [Usages](#usages)
    - [Order Queries](#order-queries)
    - [Search Queries](#search-queries)
        + [Search with wildcard](#search-with-wildcard)
        + [Search with JSON path](#search-with-json-path)
        + [Search with Relations](#search-with-relations)
    - [Taxonomy Queries](#taxonomy-queries)

## Installation

To install through composer, simply put the following in your `composer.json` file:

```json
{
    "require": {
        "laravie/query-filter": "^1.0"
    }
}
```

And then run `composer install` from the terminal.

### Quick Installation

Above installation can also be simplify by using the following command:

    composer require "laravie/query-filter=^1.0"

## Usages

### Order Queries

```php
new Laravie\QueryFilter\Orderable(?string $column, string $direction = 'asc', array $config = []);
```

The class provides a simple interface to handle `ORDER BY` queries to Laravel Eloquent/Query Builder.

```php
use App\User;
use Laravie\QueryFilter\Orderable;

$query = App\User::query();

$orderable = new Orderable(
    'name', 'desc'
);

return $orderable->apply($query)->get(); 
```

```sql
select * FROM `users` ORDER BY `name` DESC;
```

> The code will validate the column name before trying to apply `orderBy()` to the query, this would prevent SQL injection especially when column is given by the user.

### Search Queries

```php
new Laravie\QueryFilter\Searchable(?string $keyword, array $columns = []);
```

The class provides a simple interface to `LIKE` queries to Laravel Eloquent/Query Builder.

```php
use App\User;
use Laravie\QueryFilter\Searchable;

$query = App\User::query();

$searchable = new Searchable(
    'crynobone', ['name', 'email']
);

return $searchable->apply($query)->get(); 
```

```sql
select * from `users` 
where (
    (
        `name` like 'crynobone' 
        or `name` like 'crynobone%'
        or `name` like '%crynobone'
        or `name` like '%crynobone%'
    ) or (
        `email` like 'crynobone' 
        or `email` like 'crynobone%'
        or `email` like '%crynobone'
        or `email` like '%crynobone%'
    )
);
```

#### Search with wildcard

Set specific `%` or `*` wildcard to reduce the possible `LIKE`s variations.

```php
use App\User;
use Laravie\QueryFilter\Searchable;

$query = App\User::query();

$searchable = new Searchable(
    'crynobone*gmail', ['name', 'email']
);

return $searchable->apply($query)->get(); 
```

```sql
select * from `users` 
where (
    (
        `name` like 'crynobone%gmail'
    ) or (
        `email` like 'crynobone%gmail'
    )
);
```

#### Search with JSON path

This would allow you to query JSON path using `LIKE` with case insensitive (JSON path in MySQL is case-sensitive by default).

```php
use App\User;
use Laravie\QueryFilter\Searchable;

$query = App\User::query();

$searchable = new Searchable(
    'Malaysia', ['address->country']
);

return $searchable->apply($query)->get(); 
```

```sql
select * from `users` 
where (
    (
        lower(`address`->'$.country') like 'malaysia'
        or lower(`address`->'$.country') like 'malaysia%'
        or lower(`address`->'$.country') like '%malaysia'
        or lower(`address`->'$.country') like '%malaysia%'
    )
)
```

#### Search with Relations

This would make it easy to search results not only in the current model but also it's relations.

```php
use App\User;
use Laravie\QueryFilter\Searchable;

$query = App\User::query();

$searchable = new Searchable(
    'Administrator', ['name', 'roles.name']
);

return $searchable->apply($query)->get(); 
```

```sql
select * from `users` 
where (
    (
        `name` like 'Administrator' 
        or `name` like 'Administrator%'
        or `name` like '%Administrator'
        or `name` like '%Administrator%'
    ) or exists (
        select * from `roles` 
        inner join `user_role` 
            on `roles`.`id` = `user_role`.`role_id` 
        where `users`.`id` = `user_role`.`user_id` 
            and (
                `name` like 'Administrator' 
                or `name` like 'Administrator%' 
                or `name` like '%Administrator' 
                or `name` like '%Administrator%'
            )
        )
)
```

> Relations search can only be applied to `Illuminate\Database\Eloquent\Builder` as it need to ensure that the relationship exists via `whereHas()` queries.

### Taxonomy Queries

```php
new Laravie\QueryFilter\Taxonomy(?string $keyword, array $rules, array $columns = []);
```

```php
use App\User;
use Laravie\QueryFilter\Taxonomy;

$query = App\User::query();

$taxonomy = new Taxonomy(
    'is:admin email:crynobone@gmail.com', [
        'email:*' => static function ($query, $value) {
            return $query->where('email', '=', $value);
        },
        'role:[]' => static function ($query, array $value) {
            return $query->whereIn('role', $value);
        },
        'is:admin' => static function ($query) {
            return $query->where('admin', '=', 1);
        },
    ],
);

$taxonomy->apply($query)->get();
```

```sql
select * from `user` 
where `email`='crynobone@gmail.com'
and `admin`=1;
```
