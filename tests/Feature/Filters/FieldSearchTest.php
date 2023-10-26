<?php

namespace Laravie\QueryFilter\Tests\Feature\Filters;

use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Laravie\QueryFilter\Filters\FieldSearch;
use Laravie\QueryFilter\Searchable;
use Laravie\QueryFilter\Tests\TestCase;
use Mockery as m;
use PHPUnit\Framework\Attributes\Test;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

class FieldSearchTest extends TestCase
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

    #[Test]
    public function it_can_build_search_query_with_empty_keyword()
    {
        UserFactory::new()->times(5)->create([
            'name' => 'hello world',
        ]);

        UserFactory::new()->times(3)->create([
            'name' => 'goodbye world',
        ]);

        $stub = new FieldSearch('name');

        $query = m::spy(EloquentQueryBuilder::class);

        $query->shouldNotHaveReceived('orWhere');

        $stub->apply($query, [], 'like', 'orWhere');
    }
}
