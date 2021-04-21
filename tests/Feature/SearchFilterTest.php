<?php

namespace Laravie\QueryFilter\Tests\Feature;

use Laravie\QueryFilter\Contracts\Filter\RequiresEloquent;
use Laravie\QueryFilter\Contracts\Filter\RequiresFluent;
use function Laravie\QueryFilter\connection_type;
use function Laravie\QueryFilter\like_operator;
use Laravie\QueryFilter\SearchFilter;
use Laravie\QueryFilter\Tests\Models\User;
use Laravie\QueryFilter\Tests\TestCase;

class SearchFilterTest extends TestCase
{
    /** @test */
    public function it_can_trigger_exception_when_function_expecting_eloquent_query_builder()
    {
        $search = new class() extends SearchFilter implements RequiresEloquent {
            public function apply($query, array $keywords, string $likeOperator, string $whereOperator)
            {
                //
            }
        };

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Unable to use '.class_basename($search).' when $query is not an instance of Illuminate\Database\Eloquent\Builder');

        $query = User::query()->toBase();

        $search->validate($query);
    }

    /** @test */
    public function it_can_trigger_exception_when_function_expecting_fluent_query_builder()
    {
        $search = new class() extends SearchFilter implements RequiresFluent {
            public function apply($query, array $keywords, string $likeOperator, string $whereOperator)
            {
                //
            }
        };

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Unable to use '.class_basename($search).' when $query is not an instance of Illuminate\Database\Query\Builder');

        $query = User::query();

        $search->validate($query);
    }
}
