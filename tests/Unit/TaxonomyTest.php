<?php

namespace Laravie\QueryFilter\Tests\Unit;

use Laravie\QueryFilter\Taxonomy;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class TaxonomyTest extends TestCase
{
    /** @test */
    public function it_can_build_match_query()
    {
        $query = m::mock('Illuminate\Database\Query\Builder');

        $query->shouldReceive('unless')->once()->with(false, m::type('Closure'))
            ->andReturnUsing(static function ($b, $c) use ($query) {
                if (! $b) {
                    return $c($query);
                }
            })
            ->shouldReceive('when')->once()->with(true, m::type('Closure'))
            ->andReturnUsing(static function ($b, $c) use ($query) {
                if ((bool) $b) {
                    return $c($query);
                }
            })
            ->shouldReceive('where')->once()->with('name', '=', 'hello')->andReturnSelf()
            ->shouldReceive('whereIn')->once()->with('email', ['crynobone@gmail.com', 'crynobone@orchestraplatform.com'])->andReturnSelf()
            ->shouldReceive('whereNotNull')->once()->with('deleted_at')->andReturnSelf();

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

        $this->assertEquals($query, $stub->apply($query));
    }

    /** @test */
    public function it_cant_build_match_query_given_empty_taxanomy()
    {
        $query = m::mock('Illuminate\Database\Query\Builder');

        $query->shouldReceive('unless')->once()->with(true, m::type('Closure'))
            ->andReturnUsing(static function ($b, $c) use ($query) {
                if (! $b) {
                    return $c($query);
                }
            })
            ->shouldReceive('when')->once()->with(false, m::type('Closure'))
            ->andReturnUsing(static function ($b, $c) use ($query) {
                if ((bool) $b) {
                    return $c($query);
                }

                return $query;
            });

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

        $this->assertEquals($query, $stub->apply($query));
    }

    /** @test */
    public function it_can_build_match_query_with_basic_search()
    {
        $query = m::mock('Illuminate\Database\Query\Builder');

        $query->shouldReceive('getConnection->getDriverName')->andReturn('mysql');
        $query->shouldReceive('when')->once()->with(false, m::type('Closure'))->andReturnSelf()
            ->shouldReceive('where')->once()->with(m::type('Closure'))
            ->andReturnUsing(static function ($c) use ($query) {
                $c($query);
            })
            ->shouldReceive('orWhere')->once()->with(m::type('Closure'))
            ->andReturnUsing(static function ($c) use ($query) {
                $c($query);
            })
            ->shouldReceive('orWhere')->once()->with('name', 'like', 'hello')
            ->shouldReceive('orWhere')->once()->with('name', 'like', 'hello%')
            ->shouldReceive('orWhere')->once()->with('name', 'like', '%hello')
            ->shouldReceive('orWhere')->once()->with('name', 'like', '%hello%');

        $stub = new Taxonomy(
            'hello', [], ['name']
        );

        $this->assertEquals($query, $stub->apply($query));
    }
}
