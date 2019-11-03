
Database/Eloquent Query Builder filters for Laravel
==============

[![Build Status](https://travis-ci.org/laravie/query-filter.svg?branch=master)](https://travis-ci.org/laravie/query-filter)
[![Latest Stable Version](https://poser.pugx.org/laravie/query-filter/v/stable)](https://packagist.org/packages/laravie/query-filter)
[![Total Downloads](https://poser.pugx.org/laravie/query-filter/downloads)](https://packagist.org/packages/laravie/query-filter)
[![Latest Unstable Version](https://poser.pugx.org/laravie/query-filter/v/unstable)](https://packagist.org/packages/laravie/query-filter)
[![License](https://poser.pugx.org/laravie/query-filter/license)](https://packagist.org/packages/laravie/query-filter)
[![Coverage Status](https://coveralls.io/repos/github/laravie/query-filter/badge.svg?branch=master)](https://coveralls.io/github/laravie/query-filter?branch=master)

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

### Search Queries

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
    `name` like 'crynobone%gmail'
) or (
    `email` like 'crynobone%gmail'
);
```

#### Search with JSON path

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
    lower(`address`->'$.country') like 'malaysia'
)
```

#### Search with Relations

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
        select * from "roles" 
        inner join "user_role" 
            on "roles"."id" = "user_role"."role_id" 
        where "users"."id" = "user_role"."user_id" 
            and (
                "name" like 'Administrator' 
                or "name" like 'Administrator%' 
                or "name" like '%Administrator' 
                or "name" like '%Administrator%'
            )
        )
)
```

