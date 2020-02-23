
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
* [Integrations](integrations)
    - [Query Builder Macro](#query-builder-macro)
    - [Using with Laravel Nova](#using-with-laravel-nova)

## Installation

To install through composer, run the following command from terminal:

    composer require "laravie/query-filter"

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
select * from `users` order by `name` desc;
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
);
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
);
```

> Relations search can only be applied to `Illuminate\Database\Eloquent\Builder` as it need to ensure that the relationship exists via `whereHas()` queries.

### Taxonomy Queries

```php
new Laravie\QueryFilter\Taxonomy(?string $keyword, array $rules, array $columns = []);
```

Taxonomy always developers to create a set of rules to group the search keywords using `WHERE ... AND`. For any un-grouped keyword it will be executed via `Laravie\QueryFilter\Searchable` based on given `$columns`.

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

## Integrations

### Query Builder Macro

You can integrate `Searchable` with database or eloquent query builder macro by adding the following code to your `AppServiceProvider` (under `register` method):

```php
<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Laravie\QueryFilter\Searchable;

class AppServiceProvider extends \Illuminate\Support\ServiceProvider 
{
    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register()
    {
        QueryBuilder::macro('whereLike', static function ($attributes, string $searchTerm) {
            return (new Searchable($searchTerm, Arr::wrap($attributes)))->apply($this);
        });

        EloquentBuilder::macro('whereLike', static function ($attributes, string $searchTerm) {
            return (new Searchable($searchTerm, Arr::wrap($attributes)))->apply($this);
        });
    }
}
```

### Using with Laravel Nova

You can override the default Laravel global and local search feature by adding the following methods on `app/Nova/Resource.php`:

```php
<?php

namespace App\Nova;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource as NovaResource;
use Laravie\QueryFilter\Taxonomy;

abstract class Resource extends NovaResource
{
    // ...
    
    /**
     * Apply the search query to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $search
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected static function applySearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            static::applyResourceSearch($query, $search);
        });
    }

    /**
     * Apply the search query to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $search
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected static function applyResourceSearch($query, $search)
    {
        (new Taxonomy(
            $search, static::taxonomiesRules(), static::searchableColumns()
        ))->apply($query);

        return $query;
    }

    /**
     * Taxonomies Rules.
     *
     * @return array 
     */
    public static function taxonomiesRules()
    {
        return [];
    }
}
```
