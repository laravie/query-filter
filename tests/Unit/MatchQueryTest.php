<?php

namespace Laravie\QueryFilter\Tests\Unit;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Laravie\QueryFilter\MatchQuery;

class MatchQueryTest extends TestCase
{
    /** @testx */
    public function it_can_build_match_query()
    {
        $query = m::mock('Illuminate\Database\Query\Builder');

        $query->shouldReceive('unless')->once()->with(false, m::type('Closure'))
                ->andReturnUsing(static function ($b, $c) use ($query) {
                    $c($query);
                })
            ->shouldReceive('where')->once()->with('name', '=', 'hello');

        $stub = new MatchQuery(
            'name:hello', [
                'name:*' => static function ($query, $value) {
                    return $query->where('name', '=', $value);
                }
            ], ['name']
        );

        $this->assertEquals($query, $stub->apply($query));
    }

    /** @test */
    public function it_can_build_match_query_with_basic_search()
    {
        $query = m::mock('Illuminate\Database\Query\Builder');

        $query->shouldReceive('getConnection->getDriverName')->andReturn('mysql');
        $query->shouldReceive('orWhere')->once()->with(m::type('Closure'))
                ->andReturnUsing(static function ($c) use ($query) {
                    $c($query);
                })
            ->shouldReceive('orWhere')->once()->with('name', 'like', 'hello')
            ->shouldReceive('orWhere')->once()->with('name', 'like', 'hello%')
            ->shouldReceive('orWhere')->once()->with('name', 'like', '%hello')
            ->shouldReceive('orWhere')->once()->with('name', 'like', '%hello%');

        $stub = new MatchQuery(
            'hello', [], ['name']
        );

        $this->assertEquals($query, $stub->apply($query));
    }
}
