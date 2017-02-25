<?php

namespace DTL\WorseReflection\Tests\Unit;

use DTL\WorseReflection\SourceContext;
use DTL\WorseReflection\Type;
use DTL\WorseReflection\ClassName;
use Prophecy\Argument;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node;
use PhpParser\Node\Scalar;
use PhpParser\Node\Expr;

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
     * @dataProvider providefromstring
     */
    public function testFromString($string, Type $expectedType)
    {
        $type = Type::fromString($this->context->reveal(), $string);
        $this->assertEquals($type, $expectedType);
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
                Type::class(ClassName::fromString('\stdClass')),
            ],
            [
                '',
                Type::unknown(),
            ],
        ];
    }

    /**
     * @dataProvider provideFromParserNode
     */
    public function testFromParserNode(Node $node, Type $expectedType)
    {
        $type = Type::fromParserNode($this->context->reveal(), $node);
        $this->assertEquals($type, $expectedType);
    }

    public function provideFromParserNode()
    {
        return [
            [
                new Scalar\String_('hello'),
                Type::string(),
            ],
            [
                new Scalar\DNumber(12.12),
                Type::float(),
            ],
            [
                new Scalar\LNumber(12),
                Type::int(),
            ],
            [
                new Scalar\Encapsed([]),
                Type::string(),
            ],
            [
                new Scalar\EncapsedStringPart(''),
                Type::string(),
            ],
            [
                new Scalar\MagicConst\Class_([]),
                Type::string(),
            ],
            [
                new Node\Param('foobar', null, 'string'),
                Type::string(),
            ],
            [
                new Node\Param('foobar', null, new Node\Name('Foo\\Bar')),
                Type::class(ClassName::fromString('Foo\\Bar')),
            ],
            [
                new Expr\New_(new Node\Name('Foo')),
                Type::class(ClassName::fromString('Foo')),
            ],
            [
                new Node\Stmt\Class_(new Node\Name('Foo')),
                Type::class(ClassName::fromString('Foo')),
            ],
        ];
    }
}
