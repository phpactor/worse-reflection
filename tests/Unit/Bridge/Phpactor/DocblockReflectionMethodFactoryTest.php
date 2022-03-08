<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\Phpactor;

use Closure;
use PHPUnit\Framework\TestCase;
use Phpactor\Docblock\DefaultValue;
use Phpactor\Docblock\DocblockType;
use Phpactor\Docblock\DocblockTypes;
use Phpactor\Docblock\Method\Parameter;
use Phpactor\Docblock\Tag\MethodTag;
use Phpactor\WorseReflection\Bridge\Phpactor\DocblockReflectionMethodFactory;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\ReflectorBuilder;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class DocblockReflectionMethodFactoryTest extends TestCase
{
    use ProphecyTrait;
    
    private DocblockReflectionMethodFactory $factory;
    
    private ObjectProphecy $docblock;

    public function setUp(): void
    {
        $this->factory = new DocblockReflectionMethodFactory();
        $this->docblock = $this->prophesize(DocBlock::class);
    }

    /**
     * @dataProvider provideCreatesDocblockMethod
     */
    public function testCreatesDocblockMethod(MethodTag $methodTag, Closure $assertion): void
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
            function (ReflectionMethod $method): void {
                $this->assertEquals('Foobar', (string) $method->class()->name());
                $this->assertEquals('myMethod', $method->name());
            }
        ];

        yield 'static method' => [
            new MethodTag(
                DocblockTypes::fromStringTypes([]),
                'myMethod',
                [],
                true
            ),
            function (ReflectionMethod $method): void {
                $this->assertEquals('Foobar', (string) $method->class()->name());
                $this->assertEquals('myMethod', $method->name());
                $this->assertTrue($method->isStatic());
            }
        ];

        yield 'multiple types' => [
            new MethodTag(
                DocblockTypes::fromStringTypes(['Foobar', 'string']),
                'myMethod'
            ),
            function (ReflectionMethod $method): void {
                $this->assertEquals('Foobar', (string) $method->class()->name());
                $this->assertEquals('myMethod', $method->name());
                $this->assertEquals(Types::fromTypes([
                    Type::fromString('Foobar'),
                    Type::string(),
                ]), $method->inferredTypes());
            }
        ];

        yield 'parameters' => [
            new MethodTag(
                DocblockTypes::fromStringTypes(['Foobar', 'string']),
                'myMethod',
                [
                    new Parameter('one'),
                    new Parameter('two'),
                ]
            ),
            function (ReflectionMethod $method): void {
                $this->assertEquals(2, $method->parameters()->count());
                $this->assertEquals('one', $method->parameters()->first()->name());
                $this->assertEquals('two', $method->parameters()->get('two')->name());
            }
        ];

        yield 'parameters with type and default value' => [
            new MethodTag(
                DocblockTypes::fromStringTypes(['Foobar', 'string']),
                'myMethod',
                [
                    new Parameter('one', DocblockTypes::fromDocblockTypes([
                        DocblockType::of('string'),
                        DocblockType::of('int'),
                    ], DefaultValue::ofValue(1234)))
                ]
            ),
            function (ReflectionMethod $method): void {
                $this->assertEquals(1, $method->parameters()->count());
                $parameter = $method->parameters()->first();
                $this->assertEquals('one', $parameter->name());
                $this->assertCount(2, $parameter->inferredTypes());
                $this->assertEquals('string', $parameter->inferredTypes()->best());
            }
        ];
    }
}
