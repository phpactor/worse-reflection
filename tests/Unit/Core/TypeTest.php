<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ClassName;

class TypeTest extends TestCase
{
    /**
     * @testdox It should __toString the given type.
     * @dataProvider provideToString
     */
    public function testToString($type, $expected, $primitive)
    {
        $type = Type::fromString($type);
        $this->assertEquals($expected, (string) $type);

        if ($type->isDefined()) {
            $this->assertEquals($primitive, $type->primitive());
        }
    }

    public function provideToString()
    {
        return [
            [
                'string',
                'string',
                'string',
            ],
            [
                'float',
                'float',
                'float',
            ],
            [
                'int',
                'int',
                'int',
            ],
            [
                'bool',
                'bool',
                'bool',
            ],
            [
                'array',
                'array',
                'array',
            ],
            [
                'Foobar',
                'Foobar',
                'object'
            ],
            [
                'mixed',
                '<unknown>',
                '<unknown>'
            ],
        ];
    }

    /**
     * @testdox It returns the short name for a class.
     */
    public function testShort()
    {
        $type = Type::fromString('Foo\Bar\Bar');
        $this->assertEquals('Bar', $type->short());
    }

    /**
     * @testdox It returns the "short" name for a primitive.
     */
    public function testShortPrimitive()
    {
        $type = Type::fromString('string');
        $this->assertEquals('string', $type->short());
    }

    /**
     * @testdox It has descriptors to say if it is a class or primitive.
     */
    public function testReturnsIfClass()
    {
        $type = Type::fromString('Foo\Bar');
        $this->assertTrue($type->isClass());
        $this->assertFalse($type->isPrimitive());

        $type = Type::fromString('string');
        $this->assertFalse($type->isClass());
        $this->assertTrue($type->isPrimitive());
    }

    /**
     * @testdox It can be created from a value
     * @dataProvider provideValues
     */
    public function testFromValue($value, Type $expectedType)
    {
        $type = Type::fromValue($value);
        $this->assertEquals($expectedType, $type);
    }

    public function provideValues()
    {
        return [
            [
                'string',
                Type::string(),
            ],
            [
                11,
                Type::int(),
            ],
            [
                11.2,
                Type::float(),
            ],
            [
                [],
                Type::array(),
            ],
            [
                true,
                Type::bool(),
            ],
            [
                false,
                Type::bool(),
            ],
            [
                null,
                Type::null(),
            ],
            [
                new \stdClass(),
                Type::class(ClassName::fromString('stdClass')),
            ],
        ];
    }
}
