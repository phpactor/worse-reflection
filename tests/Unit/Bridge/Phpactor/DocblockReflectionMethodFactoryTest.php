<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\Phpactor;

use Closure;
use PHPUnit\Framework\TestCase;
use Phpactor\Docblock\DocblockTypes;
use Phpactor\Docblock\Method\Parameter;
use Phpactor\Docblock\Tag\MethodTag;
use Phpactor\WorseReflection\Bridge\Phpactor\DocblockReflectionMethodFactory;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\ReflectorBuilder;

class DocblockReflectionMethodFactoryTest extends TestCase
{
    /**
     * @var DocblockReflectionMethodFactory
     */
    private $factory;

    /**
     * @var ObjectProphecy
     */
    private $docblock;

    public function setUp()
    {
        $this->factory = new DocblockReflectionMethodFactory();
        $this->docblock = $this->prophesize(DocBlock::class);
    }

    /**
     * @dataProvider provideCreatesDocblockMethod
     */
    public function testCreatesDocblockMethod(MethodTag $methodTag, Closure $assertion)
    {
        $reflector = ReflectorBuilder::create()->addSource(
            '<?php class Foobar {}'
        )->build();
        $reflectionClass = $reflector->reflectClass('Foobar');
        $reflectionMethod = $this->factory->create($this->docblock->reveal(), $reflectionClass, $methodTag);
        $assertion($reflectionMethod);
    }

    public function provideCreatesDocblockMethod()
    {
        yield 'minimal' => [
            new MethodTag(
                DocblockTypes::fromStringTypes([]),
                'myMethod'
            ),
            function (ReflectionMethod $method) {
                $this->assertEquals('Foobar', (string) $method->class()->name());
                $this->assertEquals('myMethod', $method->name());
            }
        ];

        yield 'multiple types' => [
            new MethodTag(
                DocblockTypes::fromStringTypes(['Foobar', 'string']),
                'myMethod'
            ),
            function (ReflectionMethod $method) {
                $this->assertEquals('Foobar', (string) $method->class()->name());
                $this->assertEquals('myMethod', $method->name());
                $this->assertEquals(Types::fromTypes([
                    Type::fromString('Foobar'),
                    Type::string(),
                ]), $method->inferredTypes());
            }
        ];

        yield 'multiple types' => [
            new MethodTag(
                DocblockTypes::fromStringTypes(['Foobar', 'string']),
                'myMethod',
                [
                    new Parameter('one'),
                    new Parameter('two'),
                ]
            ),
            function (ReflectionMethod $method) {
                $this->assertEquals(2, $method->parameters()->count());
            }
        ];
    }
}
