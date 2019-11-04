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
}
