<?php

namespace Laravie\QueryFilter\Tests\Unit\Value;

use Laravie\QueryFilter\Value\Conditions;
use PHPUnit\Framework\TestCase;

class ConditionsTest extends TestCase
{
    /** @test */
    public function it_can_build_conditions()
    {
        $rules = [
            'name:*',
            'email:*',
            'work:*',
            'tags:[]',
        ];

        $conditions = Conditions::parse(
            'Orchestra Platform name:"Mior Muhammad Zaki" email:crynobone@katsana.com tags:github work:KATSANA', $rules
        );

        $this->assertSame('Orchestra Platform', $conditions->basic());

        $this->assertSame([
            'name:"Mior Muhammad Zaki"',
            'email:crynobone@katsana.com',
            'tags:github',
            'work:KATSANA',
        ], $conditions->tagged());
    }
}
