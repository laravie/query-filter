<?php

namespace Laravie\QueryFilter\Tests\Feature\Filters;

use Laravie\QueryFilter\Searchable;
use Laravie\QueryFilter\Tests\TestCase;
use Laravie\QueryFilter\Tests\Models\User;
use Laravie\QueryFilter\Filters\JsonFieldSearch;

class JsonFieldSearchTest extends TestCase
{
    /** @test */
    public function it_can_build_search_query()
    {
        $stub = new Searchable(
            '60000', [new JsonFieldSearch('address', 'postcode')]
        );

        $query = User::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "users" where ((lower(address->\'$.postcode\') like ? or lower(address->\'$.postcode\') like ? or lower(address->\'$.postcode\') like ? or lower(address->\'$.postcode\') like ?))',
            $query->toSql()
        );

        $this->assertSame(
            ['60000', '60000%', '%60000', '%60000%'],
            $query->getBindings()
        );
    }


    /** @test */
    public function it_can_build_search_query_with_nested_json_selector()
    {
        $stub = new Searchable(
            '60000', [new JsonFieldSearch('personal', 'address.postcode')]
        );

        $query = User::query();
        $stub->apply($query);

        $this->assertSame(
            'select * from "users" where ((lower(personal->\'$.address.postcode\') like ? or lower(personal->\'$.address.postcode\') like ? or lower(personal->\'$.address.postcode\') like ? or lower(personal->\'$.address.postcode\') like ?))',
            $query->toSql()
        );

        $this->assertSame(
            ['60000', '60000%', '%60000', '%60000%'],
            $query->getBindings()
        );
    }

}
