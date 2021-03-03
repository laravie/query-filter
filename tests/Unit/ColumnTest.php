<?php

namespace Laravie\QueryFilter\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Laravie\QueryFilter\Column;
use Illuminate\Database\Query\Expression;

class ColumnTest extends TestCase
{
    /**
     * @test
     * @dataProvider validColumnNameDataProvider
     */
    public function it_can_validate_column_name($given)
    {
        $this->assertTrue((new Column($given))->validate());
    }

    /**
     * @test
     * @dataProvider invalidColumnNameDataProvider
     */
    public function it_cant_validate_invalid_column_name($given)
    {
        $this->assertFalse((new Column($given))->validate());
    }

    /**
     * Valid column name data provider.
     *
     * @return array
     */
    public function validColumnNameDataProvider()
    {
        return [
            [new Expression('users.fullname')],
            ['fullname'],
            [\str_pad('email', 64, 'x')],
        ];
    }

    /**
     * Valid column name data provider.
     *
     * @return array
     */
    public function invalidColumnNameDataProvider()
    {
        return [
            ['email->"%27))%23injectedSQL'],
            [\str_pad('email', 65, 'x')],
            [''],
            [null],
        ];
    }
}
