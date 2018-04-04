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
    public function testToString(Type $type, $expected, $primitive)
    {
        $this->assertEquals($expected, (string) $type);

        if ($type->isDefined()) {
            $this->assertEquals($primitive, $type->primitive());
        }
    }

    public function provideToString()
    {
        return [
            [
                Type::fromString('string'),
                'string',
                'string',
            ],
            [
                Type::fromString('float'),
                'float',
                'float',
            ],
            [
                Type::fromString('int'),
                'int',
                'int',
            ],
            [
                Type::fromString('bool'),
                'bool',
                'bool',
            ],
            [
                Type::fromString('array'),
                'array',
                'array',
            ],
            [
                Type::fromString('void'),
                'void',
                'void',
            ],
            [
                Type::fromString('Foobar'),
                'Foobar',
                'object'
            ],
            [
                Type::fromString('mixed'),
                '<unknown>',
                '<unknown>'
            ],
            'Collection' => [
                Type::collection('Foobar', Type::string()),
                'Foobar<string>',
                'object',
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

        $type = Type::collection('MyCollection', 'string');
        $this->assertTrue($type->isClass());
        $this->assertFalse($type->isPrimitive());
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
