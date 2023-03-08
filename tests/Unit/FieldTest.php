<?php

namespace Laravie\QueryFilter\Tests\Unit;

use Illuminate\Database\Query\Expression;
use Laravie\QueryFilter\Field;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider validFieldNameDataProvider
     */
    public function it_can_validate_field_name($given)
    {
        $this->assertTrue((new Field($given))->validate());
    }

    /**
     * @test
     *
     * @dataProvider invalidFieldNameDataProvider
     */
    public function it_cant_validate_invalid_field_name($given)
    {
        $this->assertFalse((new Field($given))->validate());
    }

    /**
     * Valid column name data provider.
     *
     * @return array
     */
    public static function validFieldNameDataProvider()
    {
        return [
            [new Expression('users.fullname')],
            ['fullname'],
            [str_pad('email', 64, 'x')],
        ];
    }

    /**
     * Valid column name data provider.
     *
     * @return array
     */
    public static function invalidFieldNameDataProvider()
    {
        return [
            [str_pad('email', 65, 'x')],
            [''],
            [null],
        ];
    }
}
