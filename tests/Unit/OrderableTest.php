<?php

namespace Laravie\QueryFilter\Tests\Unit;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Laravie\QueryFilter\Orderable;

class OrderableTest extends TestCase
{
    /** @test */
    public function it_can_build_ordered_query()
    {
        $query = m::mock('Illuminate\Database\Query\Builder');

        $query->shouldReceive('orderBy')->once()->with('updated_at', 'DESC')->andReturnSelf();

        $stub = new Orderable('updated', 'desc');

        $this->assertEquals($query, $stub->apply($query));
    }

    /** @test */
    public function it_can_build_ordered_query_given_excluded_column()
    {
        $query = m::mock('Illuminate\Database\Query\Builder');

        $query->shouldReceive('orderBy')->once()->with('created_at', 'DESC')->andReturnSelf();

        $stub = new Orderable('created', 'desc', [
            'only' => ['created_at'],
        ]);

        $this->assertEquals($query, $stub->apply($query));
    }

    /** @test */
    public function it_doesnt_build_ordered_query_given_excluded_column()
    {
        $query = m::mock('Illuminate\Database\Query\Builder');

        $query->shouldReceive('orderBy')->never()->with('password', 'DESC')->andReturnSelf();

        $stub = new Orderable('password', 'desc', [
            'except' => ['password'],
        ]);

        $this->assertEquals($query, $stub->apply($query));
    }

    /**
     * @test
     * @dataProvider invalidColumnNameDataProvider
     */
    public function it_doesnt_build_ordered_query_given_invalid_column_name($given)
    {

        $query = m::mock('Illuminate\Database\Query\Builder');

        $query->shouldReceive('orderBy')->never()->with($given, 'ASC')->andReturnSelf();

        $this->assertEquals($query, (new Orderable($given))->apply($query));
    }

    /**
     * Invalid column name data provider.
     *
     * @return array
     */
    public function invalidColumnNameDataProvider()
    {
        return [
            ['email->"%27))%23injectedSQL'],
            [\str_pad('email', 65, 'x')],
            [''],
        ];
    }
}
