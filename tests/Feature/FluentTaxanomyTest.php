<?php

namespace Laravie\QueryFilter\Tests\Feature;

use Laravie\QueryFilter\Taxonomy;
use Illuminate\Support\Facades\DB;
use Laravie\QueryFilter\Tests\TestCase;

class FluentTaxonomyTest extends TestCase
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

        $query = DB::table('users');
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

        $query = DB::table('users');
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

        $query = DB::table('users');
        $stub->apply($query);

        $this->assertSame(
            'select * from "users" where (("name" like ? or "name" like ? or "name" like ? or "name" like ?))',
            $query->toSql()
        );

        $this->assertSame(
            ['hello', 'hello%', '%hello', '%hello%'],
            $query->getBindings()
        );
    }
}
