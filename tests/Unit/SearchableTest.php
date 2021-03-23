<?php

namespace Laravie\QueryFilter\Tests\Unit;

use Illuminate\Database\Query\Expression;
use Laravie\QueryFilter\Keyword;
use Laravie\QueryFilter\Searchable;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class SearchableTest extends TestCase
{
    /** @test */
    public function it_has_proper_signature()
    {
        $stub = new Searchable(
            'Hello', ['name']
        );

        $this->assertInstanceOf(Keyword::class, $stub->searchKeyword());
        $this->assertSame(['Hello', 'Hello%', '%Hello', '%Hello%'], $stub->searchKeyword()->all());
        $this->assertSame(['hello', 'hello%', '%hello', '%hello%'], $stub->searchKeyword()->allLowerCase());
    }

    /** @test */
    public function it_can_build_search_query()
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

        $stub = new Searchable(
            'hello', ['name']
        );

        $this->assertEquals($query, $stub->apply($query));
    }

    /** @test */
    public function it_ignores_build_search_query_when_columns_is_not_provided()
    {
        $query = m::mock('Illuminate\Database\Query\Builder');

        $query->shouldReceive('getConnection->getDriverName')->andReturn('mysql');
        $query->shouldReceive('when')->once()->with(m::type('Closure'))
            ->shouldReceive('orWhere')->never()->with(m::type('Closure'))
                ->andReturnUsing(static function ($c) use ($query) {
                    $c($query);
                })
            ->shouldReceive('orWhere')->never()->with('name', 'like', 'hello')
            ->shouldReceive('orWhere')->never()->with('name', 'like', 'hello%')
            ->shouldReceive('orWhere')->never()->with('name', 'like', '%hello')
            ->shouldReceive('orWhere')->never()->with('name', 'like', '%hello%');

        $stub = new Searchable(
            'hello', []
        );

        $this->assertEquals($query, $stub->apply($query));
    }

    /** @test */
    public function it_ignores_build_search_query_when_columns_is_invalid()
    {
        $query = m::mock('Illuminate\Database\Query\Builder');

        $query->shouldReceive('getConnection->getDriverName')->andReturn('mysql');
        $query->shouldReceive('when')->once()->with(m::type('Closure'))
            ->shouldReceive('orWhere')->never()->with(m::type('Closure'))
                ->andReturnUsing(static function ($c) use ($query) {
                    $c($query);
                })
            ->shouldReceive('orWhere')->never()->with('name', 'like', 'hello')
            ->shouldReceive('orWhere')->never()->with('name', 'like', 'hello%')
            ->shouldReceive('orWhere')->never()->with('name', 'like', '%hello')
            ->shouldReceive('orWhere')->never()->with('name', 'like', '%hello%');

        $stub = new Searchable(
            'hello', ['']
        );

        $this->assertEquals($query, $stub->apply($query));
    }

    /** @test */
    public function it_ignores_build_search_query_when_keyword_is_empty()
    {
        $query = m::mock('Illuminate\Database\Query\Builder');

        $query->shouldReceive('getConnection->getDriverName')->andReturn('mysql');
        $query->shouldReceive('when')->once()->with(m::type('Closure'))
            ->shouldReceive('orWhere')->never()->with(m::type('Closure'))
                ->andReturnUsing(static function ($c) use ($query) {
                    $c($query);
                })
            ->shouldReceive('orWhere')->never()->with('name', 'like', 'hello')
            ->shouldReceive('orWhere')->never()->with('name', 'like', 'hello%')
            ->shouldReceive('orWhere')->never()->with('name', 'like', '%hello')
            ->shouldReceive('orWhere')->never()->with('name', 'like', '%hello%');

        $stub = new Searchable(
            '', ['name']
        );

        $this->assertEquals($query, $stub->apply($query));
    }

    /** @test */
    public function it_can_build_search_query_with_expression_value()
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
            ->shouldReceive('orWhere')->once()->with('users.name', 'like', 'hello')
            ->shouldReceive('orWhere')->once()->with('users.name', 'like', 'hello%')
            ->shouldReceive('orWhere')->once()->with('users.name', 'like', '%hello')
            ->shouldReceive('orWhere')->once()->with('users.name', 'like', '%hello%');

        $stub = new Searchable(
            'hello', [new Expression('users.name')]
        );

        $this->assertEquals($query, $stub->apply($query));
    }

    /** @test */
    public function it_can_build_search_query_with_json_selector()
    {
        $query = m::mock('Illuminate\Database\Database\Builder');
        $grammar = m::mock('Illuminate\Database\Database\Grammars\Grammar');

        $query->shouldReceive('getConnection->getDriverName')->andReturn('mysql');
        $query->shouldReceive('getGrammar')->andReturn($grammar);
        $query->shouldReceive('when')->once()->with(false, m::type('Closure'))->andReturnSelf()
            ->shouldReceive('where')->once()->with(m::type('Closure'))
                ->andReturnUsing(static function ($c) use ($query) {
                    $c($query);
                })
            ->shouldReceive('orWhere')->once()->with(m::type('Closure'))
                ->andReturnUsing(static function ($c) use ($query) {
                    $c($query);
                })
            ->shouldReceive('orWhereRaw')->once()->with('lower(address->\'$.postcode\') like ?', ['hello'])
            ->shouldReceive('orWhereRaw')->once()->with('lower(address->\'$.postcode\') like ?', ['hello%'])
            ->shouldReceive('orWhereRaw')->once()->with('lower(address->\'$.postcode\') like ?', ['%hello'])
            ->shouldReceive('orWhereRaw')->once()->with('lower(address->\'$.postcode\') like ?', ['%hello%']);

        $grammar->shouldReceive('wrap')->with('address->postcode')->andReturn(new Expression('address->\'$.postcode\''));

        $stub = new Searchable(
            'hello', ['address->postcode']
        );

        $this->assertEquals($query, $stub->apply($query));
    }

    /** @test */
    public function it_cant_build_search_query_with_invalid_column_name()
    {
        $query = m::mock('Illuminate\Database\Database\Builder');

        $query->shouldReceive('getConnection->getDriverName')->andReturn('mysql');
        $query->shouldReceive('when')->once()->with(false, m::type('Closure'))->andReturnSelf()
            ->shouldReceive('where')->once()->with(m::type('Closure'))
                ->andReturnUsing(static function ($c) use ($query) {
                    $c($query);
                })
            ->shouldReceive('orWhere')->once()->with(m::type('Closure'))
                ->andReturnUsing(static function ($c) use ($query) {
                    $c($query);
                });

        $stub = new Searchable(
            'hello', ['email->"%27))%23injectedSQL']
        );

        $this->assertEquals($query, $stub->apply($query));
    }

    /** @test */
    public function it_can_build_search_query_with_relation_field()
    {
        $query = m::mock('Illuminate\Database\Eloquent\Builder');
        $relationQuery = m::mock('Illuminate\Database\Database\Builder');

        $query->shouldReceive('getModel->getConnection->getDriverName')->andReturn('mysql');
        $query->shouldReceive('when')->once()->with(false, m::type('Closure'))->andReturnSelf()
            ->shouldReceive('where')->once()->with(m::type('Closure'))
                ->andReturnUsing(static function ($c) use ($query) {
                    $c($query);
                })
            ->shouldReceive('orWhereHas')->once()->with('posts', m::type('Closure'))
            ->andReturnUsing(static function ($r, $c) use ($relationQuery) {
                $c($relationQuery);
            });

        $relationQuery->shouldReceive('where')->once()->with(m::type('Closure'))
                ->andReturnUsing(static function ($c) use ($relationQuery) {
                    $c($relationQuery);
                })
            ->shouldReceive('orWhere')->once()->with('title', 'like', 'hello')
            ->shouldReceive('orWhere')->once()->with('title', 'like', 'hello%')
            ->shouldReceive('orWhere')->once()->with('title', 'like', '%hello')
            ->shouldReceive('orWhere')->once()->with('title', 'like', '%hello%');

        $stub = new Searchable(
            'hello', ['posts.title']
        );

        $this->assertEquals($query, $stub->apply($query));
    }
}
