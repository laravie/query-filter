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
            'is:busy',
        ];

        $keywords = Keywords::parse(
            'Orchestra Platform name:"Mior Muhammad Zaki" email:crynobone@katsana.com tags:github work:KATSANA tags:twitter is:busy', $rules
        );

        $this->assertSame(6, \count($keywords));

        $this->assertSame('Orchestra Platform', $keywords->basic());
        $this->assertSame([
            'name:"Mior Muhammad Zaki"',
            'email:crynobone@katsana.com',
            'tags:github',
            'work:KATSANA',
            'tags:twitter',
            'is:busy',
        ], $keywords->tagged());

        $this->assertSame('Mior Muhammad Zaki', $keywords->where('name:*'));
        $this->assertSame('crynobone@katsana.com', $keywords->where('email:*'));
        $this->assertSame('KATSANA', $keywords->where('work:*'));
        $this->assertSame(['github', 'twitter'], $keywords->where('tags:[]'));
        $this->assertTrue($keywords->is('is:busy'));
        $this->assertEmpty($keywords->where('foo:*'));
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

        $this->assertSame(0, \count($keywords));

        $this->assertSame('Orchestra Platform', $keywords->basic());
        $this->assertSame([], $keywords->tagged());

        $this->assertEmpty($keywords->where('name:*'));
        $this->assertEmpty($keywords->where('email:*'));
        $this->assertEmpty($keywords->where('work:*'));
        $this->assertEmpty($keywords->where('tags:[]'));
        $this->assertEmpty($keywords->where('foo:*'));
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

        $this->assertSame(5, \count($keywords));

        $this->assertSame('', $keywords->basic());
        $this->assertSame([
            'name:"Mior Muhammad Zaki"',
            'email:crynobone@katsana.com',
            'tags:github',
            'work:KATSANA',
            'tags:twitter',
        ], $keywords->tagged());

        $this->assertSame('Mior Muhammad Zaki', $keywords->where('name:*'));
        $this->assertSame('crynobone@katsana.com', $keywords->where('email:*'));
        $this->assertSame('KATSANA', $keywords->where('work:*'));
        $this->assertSame(['github', 'twitter'], $keywords->where('tags:[]'));
        $this->assertEmpty($keywords->where('foo:*'));
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

        $this->assertSame(2, \count($keywords));

        $this->assertSame('', $keywords->basic());
        $this->assertSame([
            'name:"Mior Muhammad Zaki"',
            'email:crynobone@katsana.com',
        ], $keywords->tagged());

        $this->assertSame('Mior Muhammad Zaki', $keywords->where('name:*'));
        $this->assertSame('crynobone@katsana.com', $keywords->where('email:*'));
        $this->assertEmpty($keywords->where('work:*'));
        $this->assertEmpty($keywords->where('tags:[]'));
        $this->assertEmpty($keywords->where('foo:*'));
    }
}
