<?php

namespace Laravie\QueryFilter\Tests\Feature;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Laravie\QueryFilter\Searchable;
use Laravie\QueryFilter\Tests\Models\Note;
use Laravie\QueryFilter\Tests\Models\Post;
use Laravie\QueryFilter\Tests\Models\User;
use Laravie\QueryFilter\Tests\TestCase;

class EloquentSearchableTest extends TestCase
{
    /** @test */
    public function it_can_build_search_query()
    {
        \factory(User::class, 5)->create([
            'name' => 'hello world',
        ]);

        \factory(User::class, 3)->create([
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

    /** @test */
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

    /** @test */
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

    /** @test */
    public function it_can_build_search_query_with_expression_value()
    {
        \factory(User::class, 5)->create([
            'name' => 'hello world',
        ]);

        \factory(User::class, 3)->create([
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
    public function it_can_build_search_query_with_json_selector()
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

    /** @test */
    public function it_can_build_search_query_with_relation_field()
    {
        \factory(Post::class, 3)->create([
            'title' => 'hello world',
        ]);

        \factory(Post::class, 5)->create([
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

    /** @test */
    public function it_can_build_search_query_with_polymorphic_relation_field()
    {
        Relation::morphMap([
            'users' => User::class,
        ]);

        $user = \factory(User::class)->create();
        \factory(User::class, 5)->create();

        \factory(Note::class, 3)->create([
            'notable_type' => 'users',
            'notable_id' => $user->id,
            'title' => 'hello world',
        ]);

        $stub = new Searchable(
            'hello', ['title', 'morph:notable.name']
        );

        $query = Note::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "notes" where (("notes"."title" like ? or "notes"."title" like ? or "notes"."title" like ? or "notes"."title" like ?) or (("notes"."notable_type" = ? and exists (select * from "users" where "notes"."notable_id" = "users"."id" and ("users"."name" like ? or "users"."name" like ? or "users"."name" like ? or "users"."name" like ?)))))',
            $query->toSql()
        );

        $this->assertSame(
            ['hello', 'hello%', '%hello', '%hello%', 'users', 'hello', 'hello%', '%hello', '%hello%'],
            $query->getBindings()
        );

        $this->assertSame(3, $query->count());
    }
}
