<?php

namespace Laravie\QueryFilter\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Laravie\QueryFilter\Value\Keywords;

class KeywordsTest extends TestCase
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

        $conditions = Keywords::parse(
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
