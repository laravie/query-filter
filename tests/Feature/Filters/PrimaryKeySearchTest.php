<?php

namespace Laravie\QueryFilter\Tests\Feature\Filters;

use Illuminate\Support\Facades\DB;
use Laravie\QueryFilter\Filters\PrimaryKeySearch;
use Laravie\QueryFilter\Searchable;
use Laravie\QueryFilter\Tests\TestCase;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

class PrimaryKeySearchTest extends TestCase
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
            '5', [new PrimaryKeySearch()]
        );

        $query = User::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "users" where ("users"."id" = ?)',
            $query->toSql()
        );

        $this->assertSame(
            ['5'],
            $query->getBindings()
        );

        $this->assertSame(1, $query->count());
    }

    /** @test */
    public function it_cannot_build_search_query_from_fluent_query_builder()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Unable to use PrimaryKeySearch when $query is not an instance of Illuminate\Database\Eloquent\Builder');

        $stub = new Searchable(
            '5', [new PrimaryKeySearch()]
        );

        $query = DB::table('users');
        $stub->apply($query);
    }
}
