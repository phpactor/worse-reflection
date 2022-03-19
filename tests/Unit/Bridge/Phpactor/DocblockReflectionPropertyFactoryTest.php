<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\Phpactor;

use Closure;
use PHPUnit\Framework\TestCase;
use Phpactor\Docblock\DocblockTypes;
use Phpactor\Docblock\Tag\PropertyTag;
use Phpactor\WorseReflection\Bridge\Phpactor\DocblockReflectionPropertyFactory;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\ReflectorBuilder;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class DocblockReflectionPropertyFactoryTest extends TestCase
{
    use ProphecyTrait;
    
    private DocblockReflectionPropertyFactory $factory;
    
    private ObjectProphecy $docblock;

    public function setUp(): void
    {
        $this->factory = new DocblockReflectionPropertyFactory(ReflectorBuilder::create()->build());
        $this->docblock = $this->prophesize(DocBlock::class);
    }

    /**
     * @dataProvider provideCreatesDocblockProperty
     */
    public function testCreatesDocblockProperty(PropertyTag $propertyTag, Closure $assertion): void
    {
        $reflector = ReflectorBuilder::create()->addSource(
            '<?php class Foobar {}'
        )->build();
        $reflectionClass = $reflector->reflectClass('Foobar');
        $reflectionProperty = $this->factory->create($this->docblock->reveal(), $reflectionClass, $propertyTag);
        $assertion($reflectionProperty);
    }

    public function provideCreatesDocblockProperty()
    {
        yield 'minimal' => [
            new PropertyTag(
                DocblockTypes::fromStringTypes([]),
                'myProperty'
            ),
            function (ReflectionProperty $property): void {
                $this->assertEquals('Foobar', (string) $property->class()->name());
                $this->assertEquals('myProperty', $property->name());
            }
        ];

        yield 'multiple types' => [
            new PropertyTag(
                DocblockTypes::fromStringTypes(['Foobar', 'string']),
                'myProperty'
            ),
            function (ReflectionProperty $property): void {
                $this->assertEquals('Foobar', (string) $property->class()->name());
                $this->assertEquals('myProperty', $property->name());
                $this->assertEquals('Foobar|string', $property->inferredTypes()->__toString());
            }
        ];
    }
}
