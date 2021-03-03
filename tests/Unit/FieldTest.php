<?php

namespace Laravie\QueryFilter\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Laravie\QueryFilter\Field;
use Illuminate\Database\Query\Expression;

class FieldTest extends TestCase
{
    /**
     * @test
     * @dataProvider validFieldNameDataProvider
     */
    public function it_can_validate_field_name($given)
    {
        $this->assertTrue((new Field($given))->validate());
    }

    /**
     * @test
     * @dataProvider invalidFieldNameDataProvider
     */
    public function it_cant_validate_invalid_field_name($given)
    {
        $this->assertFalse((new Field($given))->validate());
    }

    /** @test */
    public function it_can_wrap_relation_field()
    {
        $this->assertEquals(['users', new Field('fullname'), 'normal'], (new Field('users.fullname'))->wrapRelationNameAndField());
    }

    /** @test */
    public function it_can_wrap_json_selector()
    {
        $this->assertEquals(['address', 'country.code'], (new Field('address->country->code'))->wrapJsonFieldAndPath());
    }

    /**
     * Valid column name data provider.
     *
     * @return array
     */
    public function validFieldNameDataProvider()
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
    public function invalidFieldNameDataProvider()
    {
        return [
            [\str_pad('email', 65, 'x')],
            [''],
            [null],
        ];
    }
}
