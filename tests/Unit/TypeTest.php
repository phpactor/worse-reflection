<?php

namespace DTL\WorseReflection\Tests\Unit;

use DTL\WorseReflection\SourceContext;
use DTL\WorseReflection\Type;
use DTL\WorseReflection\ClassName;
use Prophecy\Argument;

class TypeTest extends \PHPUnit_Framework_TestCase
{
    private $context;

    public function setUp()
    {
        $this->context = $this->prophesize(SourceContext::class);
        $this->context->resolveClassName(Argument::type(ClassName::class))->will(function ($args) {
            return $args[0];
        });
    }

    /**
     * @dataProvider provideFromString
     */
    public function testFromString($string, Type $expectedType)
    {
        Type::fromString($this->context->reveal(), $string);
    }

    public function provideFromString()
    {
        return [
            [
                'string',
                Type::string(),
            ],
            [
                'int',
                Type::int(),
            ],
            [
                'float',
                Type::float(),
            ],
            [
                '\stdClass',
                Type::class(ClassName::fromString('stdClass')),
            ],
            [
                '',
                Type::unknown(),
            ],
        ];
    }
}
