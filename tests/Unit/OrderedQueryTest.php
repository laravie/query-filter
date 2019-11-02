<?php

namespace Laravie\QueryFilter\Tests\Unit;

use Laravie\QueryFilter\OrderedQuery;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class OrderedQueryTest extends TestCase
{
    /** @test */
    public function it_can_build_basic_query_filter()
    {
        $query1 = m::mock('Illuminate\Database\Query\Builder');

        $query1->shouldReceive('orderBy')->once()->with('updated_at', 'DESC')->andReturnSelf();

        $stub1 = new OrderedQuery('updated', 'desc');

        $this->assertEquals($query1, $stub1->apply($query1));

        $query2 = m::mock('Illuminate\Database\Query\Builder');

        $query2->shouldReceive('orderBy')->once()->with('updated_at', 'DESC')->andReturnSelf();

        $stub2 = new OrderedQuery('created', 'desc', [
            'only' => ['created_at'],
        ]);

        $this->assertEquals($query2, $stub2->apply($query2));
    }

    /** @test */
    public function it_can_build_basic_query_filter_given_column_excluded()
    {
        $query = m::mock('Illuminate\Database\Query\Builder');

        $query->shouldReceive('orderBy')->never()->with('password', 'DESC')->andReturnSelf();

        $stub = new OrderedQuery('password', 'desc', [
            'except' => ['password'],
        ]);

        $this->assertEquals($query, $stub->apply($query));
    }
}
