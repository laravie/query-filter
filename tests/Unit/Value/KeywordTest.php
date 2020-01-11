<?php

namespace Laravie\QueryFilter\Tests\Unit\Value;

use PHPUnit\Framework\TestCase;
use Laravie\QueryFilter\Value\Keyword;

class KeywordTest extends TestCase
{
    /** @test */
    public function it_can_generate_keywords()
    {
        $this->assertSame([
            'Hello',
            'Hello%',
            '%Hello',
            '%Hello%',
        ], (new Keyword('Hello'))->all());
    }

    /** @test */
    public function it_can_generate_lowercased_keywords()
    {
        $this->assertSame([
            'hello',
            'hello%',
            '%hello',
            '%hello%',
        ], (new Keyword('Hello'))->allLowerCased());
    }

    /** @test */
    public function it_can_generate_wildcard_keywords()
    {
        $this->assertSame([
            'He%world',
        ], (new Keyword('He%world'))->all());
        $this->assertSame([
            'He%world',
        ], (new Keyword('He*world'))->all());
    }

    /** @test */
    public function it_can_sanitize_wildcard_attack_keywords()
    {
        $this->assertSame([], (new Keyword('%%%%'))->all());
        $this->assertSame([], (new Keyword('% % % %'))->all());
        $this->assertSame(['h%l%o%o%l%'], (new Keyword('h*l*o*o*l*'))->all());
        $this->assertSame(['h%l%o%o%l%'], (new Keyword('h%l%o%o%l%'))->all());
        $this->assertSame([
            '__aF_D_F_N_%%R_xa%9_',
        ], (new Keyword('_[^!_%/%a?F%_D)_(F%)_%([)({}%){()}£$&N%_)$*£()$*R"_)][%](%[x])%a][$*"£$-9]_'))->all());
    }
}
