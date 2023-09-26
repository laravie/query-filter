<?php

namespace Laravie\QueryFilter\Tests\Feature;

use Laravie\QueryFilter\Taxonomy;
use Laravie\QueryFilter\Tests\TestCase;
use Workbench\App\Models\User;

class EloquentTaxonomyTest extends TestCase
{
    /** @test */
    public function it_can_build_match_query()
    {
        $stub = new Taxonomy(
            'name:hello email:crynobone@gmail.com email:crynobone@orchestraplatform.com is:active', [
                'name:*' => static function ($query, $value) {
                    return $query->where('name', '=', $value);
                },
                'email:[]' => static function ($query, $value) {
                    return $query->whereIn('email', $value);
                },
                'is:active' => static function ($query) {
                    return $query->whereNotNull('deleted_at');
                },
            ], ['name']
        );

        $query = User::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "users" where "name" = ? and "email" in (?, ?) and "deleted_at" is not null',
            $query->toSql()
        );

        $this->assertSame(
            ['hello', 'crynobone@gmail.com', 'crynobone@orchestraplatform.com'],
            $query->getBindings()
        );
    }

    /** @test */
    public function it_cant_build_match_query_given_empty_taxanomy()
    {
        $stub = new Taxonomy(
            'name: email: email: is:', [
                'name:*' => static function ($query, $value) {
                    return $query->where('name', '=', $value);
                },
                'email:[]' => static function ($query, $value) {
                    return $query->whereIn('email', $value);
                },
                'is:active' => static function ($query) {
                    return $query->whereNotNull('deleted_at');
                },
            ], ['name']
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
    public function it_can_build_match_query_with_basic_search()
    {
        $stub = new Taxonomy(
            'hello', [], ['name']
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
    }

    /** @test */
    public function it_can_build_match_query_with_basic_search_with_related_field()
    {
        $stub = new Taxonomy(
            'hello', [], ['name', 'posts.title']
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
    }
}
