<?php

namespace Laravie\QueryFilter\Tests\Unit;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Laravie\QueryFilter\SearchQuery;

class SearchQueryTest extends TestCase
{
    /** @test */
    public function it_can_build_search_query()
    {
        $query = m::mock('Illuminate\Database\Query\Builder');

        $query->shouldReceive('getConnection->getDriverName')->andReturn('mysql');
        $query->shouldReceive('where')->once()->with(m::type('Closure'))
                ->andReturnUsing(function ($c) use ($query) {
                    $c($query);
                })
            ->shouldReceive('orWhere')->once()->with(m::type('Closure'))
                ->andReturnUsing(function ($c) use ($query) {
                    $c($query);
                })
            ->shouldReceive('orWhere')->once()->with('name', 'like', 'hello')
            ->shouldReceive('orWhere')->once()->with('name', 'like', 'hello%')
            ->shouldReceive('orWhere')->once()->with('name', 'like', '%hello')
            ->shouldReceive('orWhere')->once()->with('name', 'like', '%hello%');

        $stub = new SearchQuery(
            'hello', ['name']
        );

        $this->assertEquals($query, $stub->apply($query));
    }
}
