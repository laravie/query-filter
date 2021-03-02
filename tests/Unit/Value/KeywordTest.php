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
    public function it_can_generate_exact_keyword()
    {
        $this->assertSame([
            'Hello',
        ], (new Keyword('Hello'))->all('*', '%', false));
    }

    /** @test */
    public function it_can_generate_keywords_with_empty_wildcard_character()
    {
        $this->assertSame([
            'Hello',
        ], (new Keyword('Hello'))->all(null, '%'));


        $this->assertSame([
            'Hello',
        ], (new Keyword('Hello'))->all('*', null));
    }

    /** @test */
    public function it_can_generate_keywords_for_other_langs()
    {
        $this->assertSame([
            'مرحبا',
            'مرحبا%',
            '%مرحبا',
            '%مرحبا%',
        ], (new Keyword('مرحبا'))->all());
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
    public function it_can_generate_exact_lowercased_keyword()
    {
        $this->assertSame([
            'hello',
        ], (new Keyword('Hello'))->allLowerCased('*', '%', false));
    }

    /** @test */
    public function it_can_generate_lowercased_keywords_for_other_langs()
    {
        $this->assertSame([
            'مرحبا',
            'مرحبا%',
            '%مرحبا',
            '%مرحبا%',
        ], (new Keyword('مرحبا'))->allLowerCased());
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
    public function it_can_generate_wildcard_keywords_for_other_langs()
    {
        $this->assertSame([
            'مر%العالم',
        ], (new Keyword('مر%العالم'))->all());
        $this->assertSame([
            'مر%العالم',
        ], (new Keyword('مر*العالم'))->all());
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

    /** @test */
    public function it_can_sanitize_wildcard_attack_keywords_for_other_langs()
    {
        $this->assertSame([], (new Keyword('%%%%'))->all());
        $this->assertSame([], (new Keyword('% % % %'))->all());
        $this->assertSame(['م%ر%ح%ب%ا%'], (new Keyword('م%ر%ح%ب%ا%'))->all());
        $this->assertSame(['م%ر%ح%ب%ا%'], (new Keyword('م%ر%ح%ب%ا%'))->all());
        $this->assertSame([
            '__aF_D_F_N_%%R_xa%9_',
        ], (new Keyword('_[^!_%/%a?F%_D)_(F%)_%([)({}%){()}£$&N%_)$*£()$*R"_)][%](%[x])%a][$*"£$-9]_'))->all());
    }
}
