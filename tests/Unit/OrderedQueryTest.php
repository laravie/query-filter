<?php

namespace Laravie\QueryFilter\Tests\Unit;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Laravie\QueryFilter\OrderedQuery;

class OrderedQueryTest extends TestCase
{
    /** @test */
    public function it_can_build_ordered_query()
    {
        $query = m::mock('Illuminate\Database\Query\Builder');

        $query->shouldReceive('orderBy')->once()->with('updated_at', 'DESC')->andReturnSelf();

        $stub = new OrderedQuery('updated', 'desc');

        $this->assertEquals($query, $stub->apply($query));
    }

    /** @test */
    public function it_can_build_ordered_query_given_excluded_column()
    {
        $query = m::mock('Illuminate\Database\Query\Builder');

        $query->shouldReceive('orderBy')->once()->with('updated_at', 'DESC')->andReturnSelf();

        $stub = new OrderedQuery('created', 'desc', [
            'only' => ['created_at'],
        ]);

        $this->assertEquals($query, $stub->apply($query));
    }

    /** @test */
    public function it_doesnt_build_ordered_query_given_excluded_column()
    {
        $query = m::mock('Illuminate\Database\Query\Builder');

        $query->shouldReceive('orderBy')->never()->with('password', 'DESC')->andReturnSelf();

        $stub = new OrderedQuery('password', 'desc', [
            'except' => ['password'],
        ]);

        $this->assertEquals($query, $stub->apply($query));
    }
}
