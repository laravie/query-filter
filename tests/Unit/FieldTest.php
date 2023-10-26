<?php

namespace Laravie\QueryFilter\Tests\Unit;

use Illuminate\Database\Query\Expression;
use Laravie\QueryFilter\Field;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    #[Test]
    #[DataProvider('validFieldNameDataProvider')]
    public function it_can_validate_field_name($given)
    {
        $this->assertTrue((new Field($given))->validate());
    }

    #[Test]
    #[DataProvider('invalidFieldNameDataProvider')]
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
        yield [new Expression('users.fullname')];
        yield ['fullname'];
        yield [str_pad('email', 64, 'x')];
    }

    /**
     * Valid column name data provider.
     *
     * @return array
     */
    public static function invalidFieldNameDataProvider()
    {
        yield [str_pad('email', 65, 'x')];
        yield [''];
    }
}
