<?php

namespace Laravie\QueryFilter\Tests\Feature;

use Laravie\QueryFilter\Searchable;
use Laravie\QueryFilter\Tests\TestCase;
use Illuminate\Database\Query\Expression;
use Laravie\QueryFilter\Tests\Models\User;
use Laravie\QueryFilter\Filters\PrimaryKeySearch;
use Laravie\QueryFilter\Tests\Factories\PostFactory;
use Laravie\QueryFilter\Tests\Factories\UserFactory;

class EloquentSearchableTest extends TestCase
{
    /** @test */
    public function itCanBuildSearchQuery()
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

    /** @test */
    public function itCanBuildSearchQueryWithPrimaryKeySearch()
    {
        UserFactory::new()->times(5)->create([
            'name' => 'hello world',
        ]);

        UserFactory::new()->times(3)->create([
            'name' => 'goodbye world',
        ]);

        $stub = new Searchable(
            '5', [new PrimaryKeySearch(PHP_INT_MAX, ['id']), 'name']
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

    /** @test */
    public function itIgnoresBuildSearchQueryWhenColumnsIsNotProvided()
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

    /** @test */
    public function itIgnoresBuildSearchQueryWhenColumnsIsInvalid()
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

    /** @test */
    public function itIgnoresBuildSearchQueryWhenKeywordIsEmpty()
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

    /** @test */
    public function itCanBuildSearchQueryWithExpressionValue()
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

    /** @test */
    public function itCanBuildSearchQueryWithJsonSelector()
    {
        $stub = new Searchable(
            '60000', ['address->postcode']
        );

        $query = User::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "users" where ((lower(address->\'$.postcode\') like ? or lower(address->\'$.postcode\') like ? or lower(address->\'$.postcode\') like ? or lower(address->\'$.postcode\') like ?))',
            $query->toSql()
        );

        $this->assertSame(
            ['60000', '60000%', '%60000', '%60000%'],
            $query->getBindings()
        );
    }

    /** @test */
    public function itCanBuildSearchQueryWithNestedJsonSelector()
    {
        $stub = new Searchable(
            '60000', ['personal->address->postcode']
        );

        $query = User::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "users" where ((lower(personal->\'$.address.postcode\') like ? or lower(personal->\'$.address.postcode\') like ? or lower(personal->\'$.address.postcode\') like ? or lower(personal->\'$.address.postcode\') like ?))',
            $query->toSql()
        );

        $this->assertSame(
            ['60000', '60000%', '%60000', '%60000%'],
            $query->getBindings()
        );
    }

    /** @test */
    public function itCantBuildSearchQueryWithInvalidColumnName()
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

    /** @test */
    public function itCanBuildSearchQueryWithRelationField()
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
