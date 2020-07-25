<?php

namespace Laravie\QueryFilter\Tests\Feature;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Laravie\QueryFilter\Searchable;
use Laravie\QueryFilter\Tests\TestCase;

class FluentSearchableTest extends TestCase
{
    /** @test */
    public function it_can_build_search_query()
    {
        $stub = new Searchable(
            'hello', ['name']
        );

        $query = DB::table('users');
        $stub->apply($query);

        $this->assertSame(
            'select * from "users" where (("name" like ? or "name" like ? or "name" like ? or "name" like ?))',
            $query->toSql()
        );
    }

    /** @test */
    public function it_ignores_build_search_query_when_columns_is_not_provided()
    {
        $stub = new Searchable(
            'hello', []
        );

        $query = DB::table('users');
        $stub->apply($query);

        $this->assertSame(
            'select * from "users"',
            $query->toSql()
        );
    }

    /** @test */
    public function it_ignores_build_search_query_when_columns_is_invalid()
    {
        $stub = new Searchable(
            'hello', ['']
        );

        $query = DB::table('users');
        $stub->apply($query);

        $this->assertSame(
            'select * from "users"',
            $query->toSql()
        );
    }

    /** @test */
    public function it_ignores_build_search_query_when_keyword_is_empty()
    {
        $stub = new Searchable(
            '', ['name']
        );

        $query = DB::table('users');
        $stub->apply($query);

        $this->assertSame(
            'select * from "users"',
            $query->toSql()
        );
    }

    /** @test */
    public function it_can_build_search_query_with_expression_value()
    {
        $stub = new Searchable(
            'hello', [new Expression('users.name')]
        );

        $query = DB::table('users');
        $stub->apply($query);

        $this->assertSame(
            'select * from "users" where (("users"."name" like ? or "users"."name" like ? or "users"."name" like ? or "users"."name" like ?))',
            $query->toSql()
        );
    }

    /** @test */
    public function it_can_build_search_query_with_json_selector()
    {
        $stub = new Searchable(
            'hello', ['address->postcode']
        );

        $query = DB::table('users');
        $stub->apply($query);

        $this->assertSame(
            'select * from "users" where ((lower(address->\'$.postcode\') like ? or lower(address->\'$.postcode\') like ? or lower(address->\'$.postcode\') like ? or lower(address->\'$.postcode\') like ?))',
            $query->toSql()
        );
    }

    /** @test */
    public function it_cant_build_search_query_with_invalid_column_name()
    {
        $stub = new Searchable(
            'hello', ['email->"%27))%23injectedSQL']
        );

        $query = DB::table('users');
        $stub->apply($query);

        $this->assertSame(
            'select * from "users"',
            $query->toSql()
        );
    }
}