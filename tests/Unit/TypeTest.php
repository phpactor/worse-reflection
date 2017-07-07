<?php

namespace Phpactor\WorseReflection\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Type;

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

        if (false === $type->isUnknown()) {
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
}
