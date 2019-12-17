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

        $keywords = Keywords::parse(
            'Orchestra Platform name:"Mior Muhammad Zaki" email:crynobone@katsana.com tags:github work:KATSANA tags:twitter', $rules
        );

        $this->assertTrue($keywords->hasBasic());
        $this->assertTrue($keywords->hasTagged());

        $this->assertSame('Orchestra Platform', $keywords->basic());
        $this->assertSame([
            'name:"Mior Muhammad Zaki"',
            'email:crynobone@katsana.com',
            'tags:github',
            'work:KATSANA',
            'tags:twitter',
        ], $keywords->tagged());
    }

    /** @test */
    public function it_can_build_conditions_only_contains_basic()
    {
        $rules = [
            'name:*',
            'email:*',
            'work:*',
            'tags:[]',
        ];

        $keywords = Keywords::parse(
            'Orchestra Platform', $rules
        );

        $this->assertTrue($keywords->hasBasic());
        $this->assertFalse($keywords->hasTagged());

        $this->assertSame('Orchestra Platform', $keywords->basic());
        $this->assertSame([], $keywords->tagged());
    }

    /** @test */
    public function it_can_build_conditions_only_contains_tagged()
    {
        $rules = [
            'name:*',
            'email:*',
            'work:*',
            'tags:[]',
        ];

        $keywords = Keywords::parse(
            'name:"Mior Muhammad Zaki" email:crynobone@katsana.com tags:github work:KATSANA tags:twitter', $rules
        );

        $this->assertFalse($keywords->hasBasic());
        $this->assertTrue($keywords->hasTagged());

        $this->assertSame('', $keywords->basic());
        $this->assertSame([
            'name:"Mior Muhammad Zaki"',
            'email:crynobone@katsana.com',
            'tags:github',
            'work:KATSANA',
            'tags:twitter',
        ], $keywords->tagged());
    }

    /** @test */
    public function it_can_build_conditions_only_contains_partial_tagged()
    {
        $rules = [
            'name:*',
            'email:*',
            'work:*',
            'tags:[]',
        ];

        $keywords = Keywords::parse(
            'name:"Mior Muhammad Zaki" email:crynobone@katsana.com', $rules
        );

        $this->assertFalse($keywords->hasBasic());
        $this->assertTrue($keywords->hasTagged());

        $this->assertSame('', $keywords->basic());
        $this->assertSame([
            'name:"Mior Muhammad Zaki"',
            'email:crynobone@katsana.com',
        ], $keywords->tagged());
    }
}
