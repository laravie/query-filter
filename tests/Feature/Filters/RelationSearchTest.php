<?php

namespace Laravie\QueryFilter\Tests\Feature\Filters;

use Illuminate\Support\Facades\DB;
use Laravie\QueryFilter\Filters\RelationSearch;
use Laravie\QueryFilter\Searchable;
use Laravie\QueryFilter\Tests\Factories\PostFactory;
use Laravie\QueryFilter\Tests\Models\User;
use Laravie\QueryFilter\Tests\TestCase;

class RelationSearchTest extends TestCase
{
    /** @test */
    public function it_can_build_search_query()
    {
        PostFactory::new()->times(3)->create([
            'title' => 'hello world',
        ]);

        PostFactory::new()->times(5)->create([
            'title' => 'goodbye world',
        ]);

        $stub = new Searchable(
            'hello', [new RelationSearch('posts', 'title')]
        );

        $query = User::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "users" where (exists (select * from "posts" where "users"."id" = "posts"."user_id" and ("posts"."title" like ? or "posts"."title" like ? or "posts"."title" like ? or "posts"."title" like ?)))',
            $query->toSql()
        );

        $this->assertSame(
            ['hello', 'hello%', '%hello', '%hello%'],
            $query->getBindings()
        );

        $this->assertSame(3, $query->count());
    }

    /** @test */
    public function it_cannot_build_search_query_using_fluent_query_builder()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Unable to use RelationSearch when $query is not an instance of Illuminate\Database\Eloquent\Builder');

        $stub = new Searchable(
            'hello', [new RelationSearch('posts', 'title')]
        );

        $query = DB::table('users');
        $stub->apply($query);
    }
}
