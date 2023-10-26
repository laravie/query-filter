<?php

namespace Laravie\QueryFilter\Tests\Feature;

use Illuminate\Database\Query\Expression;
use Laravie\QueryFilter\Filters\PrimaryKeySearch;
use Laravie\QueryFilter\Searchable;
use Laravie\QueryFilter\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Workbench\App\Models\User;
use Workbench\Database\Factories\PostFactory;
use Workbench\Database\Factories\UserFactory;

class EloquentSearchableTest extends TestCase
{
    #[Test]
    public function it_can_build_search_query()
    {
        UserFactory::new()->times(5)->create([
            'name' => 'hello world',
        ]);

        UserFactory::new()->times(3)->create([
            'name' => 'goodbye world',
        ]);

        $stub = new Searchable(
            'hello', ['name']
        );

        $query = User::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "users" where (("users"."name" like ? or "users"."name" like ? or "users"."name" like ? or "users"."name" like ?))',
            $query->toSql()
        );

        $this->assertSame(
            ['hello', 'hello%', '%hello', '%hello%'],
            $query->getBindings()
        );

        $this->assertSame(5, $query->count());
    }

    #[Test]
    public function it_can_build_search_query_with_custom_search_variants()
    {
        UserFactory::new()->times(5)->create([
            'name' => 'hello world',
        ]);

        UserFactory::new()->times(3)->create([
            'name' => 'goodbye world',
        ]);

        $stub = (new Searchable(
            'hello', ['name']
        ))->wildcardSearchVariants(['%{keyword}%']);

        $query = User::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "users" where ("users"."name" like ?)',
            $query->toSql()
        );

        $this->assertSame(
            ['%hello%'],
            $query->getBindings()
        );

        $this->assertSame(5, $query->count());
    }

    #[Test]
    public function it_can_build_search_query_with_combined_with_search_filters()
    {
        UserFactory::new()->times(5)->create([
            'name' => 'hello world',
        ]);

        UserFactory::new()->times(3)->create([
            'name' => 'goodbye world',
        ]);

        $stub = new Searchable(
            '5', [new PrimaryKeySearch(PHP_INT_MAX), 'name']
        );

        $query = User::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "users" where ("users"."id" = ? or ("users"."name" like ? or "users"."name" like ? or "users"."name" like ? or "users"."name" like ?))',
            $query->toSql()
        );

        $this->assertSame(
            ['5', '5', '5%', '%5', '%5%'],
            $query->getBindings()
        );

        $this->assertSame(1, $query->count());
    }

    #[Test]
    public function it_ignores_build_search_query_when_columns_is_not_provided()
    {
        $stub = new Searchable(
            'hello', []
        );

        $query = User::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "users"',
            $query->toSql()
        );

        $this->assertSame(
            [],
            $query->getBindings()
        );
    }

    #[Test]
    public function it_ignores_build_search_query_when_columns_is_invalid()
    {
        $stub = new Searchable(
            'hello', ['']
        );

        $query = User::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "users"',
            $query->toSql()
        );

        $this->assertSame(
            [],
            $query->getBindings()
        );
    }

    #[Test]
    public function it_ignores_build_search_query_when_keyword_is_empty()
    {
        $stub = new Searchable(
            '', ['name']
        );

        $query = User::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "users"',
            $query->toSql()
        );

        $this->assertSame(
            [],
            $query->getBindings()
        );
    }

    #[Test]
    public function it_can_build_search_query_with_expression_value()
    {
        UserFactory::new()->times(5)->create([
            'name' => 'hello world',
        ]);

        UserFactory::new()->times(3)->create([
            'name' => 'goodbye world',
        ]);

        $stub = new Searchable(
            'hello', [new Expression('users.name')]
        );

        $query = User::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "users" where (("users"."name" like ? or "users"."name" like ? or "users"."name" like ? or "users"."name" like ?))',
            $query->toSql()
        );

        $this->assertSame(
            ['hello', 'hello%', '%hello', '%hello%'],
            $query->getBindings()
        );

        $this->assertSame(5, $query->count());
    }

    #[Test]
    public function it_can_build_search_query_with_json_selector()
    {
        $stub = new Searchable(
            '60000', ['address->postcode']
        );

        $query = User::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "users" where ((lower(json_extract("address", \'$."postcode"\')) like ? or lower(json_extract("address", \'$."postcode"\')) like ? or lower(json_extract("address", \'$."postcode"\')) like ? or lower(json_extract("address", \'$."postcode"\')) like ?))',
            $query->toSql()
        );

        $this->assertSame(
            ['60000', '60000%', '%60000', '%60000%'],
            $query->getBindings()
        );
    }

    #[Test]
    public function it_can_build_search_query_with_nested_json_selector()
    {
        $stub = new Searchable(
            '60000', ['personal->address->postcode']
        );

        $query = User::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "users" where ((lower(json_extract("personal", \'$."address"."postcode"\')) like ? or lower(json_extract("personal", \'$."address"."postcode"\')) like ? or lower(json_extract("personal", \'$."address"."postcode"\')) like ? or lower(json_extract("personal", \'$."address"."postcode"\')) like ?))',
            $query->toSql()
        );

        $this->assertSame(
            ['60000', '60000%', '%60000', '%60000%'],
            $query->getBindings()
        );
    }

    #[Test]
    public function it_cant_build_search_query_with_invalid_column_name()
    {
        $stub = new Searchable(
            'hello', ['email->"%27))%23injectedSQL']
        );

        $query = User::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "users"',
            $query->toSql()
        );

        $this->assertSame(
            [],
            $query->getBindings()
        );
    }

    #[Test]
    public function it_can_build_search_query_with_relation_field()
    {
        PostFactory::new()->times(3)->create([
            'title' => 'hello world',
        ]);

        PostFactory::new()->times(5)->create([
            'title' => 'goodbye world',
        ]);

        $stub = new Searchable(
            'hello', ['name', 'posts.title']
        );

        $query = User::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "users" where (("users"."name" like ? or "users"."name" like ? or "users"."name" like ? or "users"."name" like ?) or exists (select * from "posts" where "users"."id" = "posts"."user_id" and ("posts"."title" like ? or "posts"."title" like ? or "posts"."title" like ? or "posts"."title" like ?)))',
            $query->toSql()
        );

        $this->assertSame(
            ['hello', 'hello%', '%hello', '%hello%', 'hello', 'hello%', '%hello', '%hello%'],
            $query->getBindings()
        );

        $this->assertSame(3, $query->count());
    }
}
