<?php

namespace Laravie\QueryFilter\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Laravie\QueryFilter\Value\Terms;

class TermsTest extends TestCase
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

        $terms = Terms::parse(
            'Orchestra Platform name:"Mior Muhammad Zaki" email:crynobone@katsana.com tags:github work:KATSANA tags:twitter is:busy', $rules
        );

        $this->assertSame(6, \count($terms));

        $this->assertSame('Orchestra Platform', $terms->basic());
        $this->assertSame([
            'name:"Mior Muhammad Zaki"',
            'email:crynobone@katsana.com',
            'tags:github',
            'work:KATSANA',
            'tags:twitter',
            'is:busy',
        ], $terms->tagged());

        $this->assertSame('Mior Muhammad Zaki', $terms->where('name:*'));
        $this->assertSame('crynobone@katsana.com', $terms->where('email:*'));
        $this->assertSame('KATSANA', $terms->where('work:*'));
        $this->assertSame(['github', 'twitter'], $terms->where('tags:[]'));
        $this->assertTrue($terms->is('is:busy'));
        $this->assertEmpty($terms->where('foo:*'));
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

        $terms = Terms::parse(
            'Orchestra Platform', $rules
        );

        $this->assertSame(0, \count($terms));

        $this->assertSame('Orchestra Platform', $terms->basic());
        $this->assertSame([], $terms->tagged());

        $this->assertEmpty($terms->where('name:*'));
        $this->assertEmpty($terms->where('email:*'));
        $this->assertEmpty($terms->where('work:*'));
        $this->assertEmpty($terms->where('tags:[]'));
        $this->assertEmpty($terms->where('foo:*'));
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

        $terms = Terms::parse(
            'name:"Mior Muhammad Zaki" email:crynobone@katsana.com tags:github work:KATSANA tags:twitter', $rules
        );

        $this->assertSame(5, \count($terms));

        $this->assertSame('', $terms->basic());
        $this->assertSame([
            'name:"Mior Muhammad Zaki"',
            'email:crynobone@katsana.com',
            'tags:github',
            'work:KATSANA',
            'tags:twitter',
        ], $terms->tagged());

        $this->assertSame('Mior Muhammad Zaki', $terms->where('name:*'));
        $this->assertSame('crynobone@katsana.com', $terms->where('email:*'));
        $this->assertSame('KATSANA', $terms->where('work:*'));
        $this->assertSame(['github', 'twitter'], $terms->where('tags:[]'));
        $this->assertEmpty($terms->where('foo:*'));
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

        $terms = Terms::parse(
            'name:"Mior Muhammad Zaki" email:crynobone@katsana.com', $rules
        );

        $this->assertSame(2, \count($terms));

        $this->assertSame('', $terms->basic());
        $this->assertSame([
            'name:"Mior Muhammad Zaki"',
            'email:crynobone@katsana.com',
        ], $terms->tagged());

        $this->assertSame('Mior Muhammad Zaki', $terms->where('name:*'));
        $this->assertSame('crynobone@katsana.com', $terms->where('email:*'));
        $this->assertEmpty($terms->where('work:*'));
        $this->assertEmpty($terms->where('tags:[]'));
        $this->assertEmpty($terms->where('foo:*'));
    }
}
