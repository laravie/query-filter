<?php

namespace Laravie\QueryFilter\Tests\Feature\Filters;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravie\QueryFilter\Filters\FieldSearch;
use Laravie\QueryFilter\Searchable;
use Laravie\QueryFilter\Tests\Factories\UserFactory;
use Laravie\QueryFilter\Tests\Models\User;
use Laravie\QueryFilter\Tests\TestCase;

class FieldSearchTest extends TestCase
{
    /** @test */
    public function it_can_build_search_query()
    {
        UserFactory::new()->times(5)->create([
            'name' => 'hello world',
        ]);

        UserFactory::new()->times(3)->create([
            'name' => 'goodbye world',
        ]);

        $stub = new Searchable(
            'hello', [new FieldSearch('name')]
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
}
