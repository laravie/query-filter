<?php

namespace Laravie\QueryFilter\Tests\Unit;

use Illuminate\Database\Query\Expression;
use Laravie\QueryFilter\Column;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ColumnTest extends TestCase
{
    #[Test]
    #[DataProvider('validColumnNameDataProvider')]
    public function it_can_validate_column_name($given)
    {
        $this->assertTrue((new Column($given))->validate());
    }

    #[Test]
    #[DataProvider('invalidColumnNameDataProvider')]
    public function it_cant_validate_invalid_column_name($given)
    {
        $this->assertFalse((new Column($given))->validate());
    }

    /**
     * Valid column name data provider.
     */
    public static function validColumnNameDataProvider()
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
    public static function invalidColumnNameDataProvider()
    {
        yield ['email->"%27))%23injectedSQL'];
        yield [str_pad('email', 65, 'x')];
        yield [''];
    }
}
