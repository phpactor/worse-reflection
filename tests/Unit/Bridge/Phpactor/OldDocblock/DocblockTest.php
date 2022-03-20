<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\Phpactor\OldDocblock;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Bridge\Phpactor\OldDocblock\DocblockFactory;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\ReflectorBuilder;

class DocblockTest extends TestCase
{
    public function testIsDefined(): void
    {
        $docblock = $this->create('');
        $this->assertFalse($docblock->isDefined());

        $docblock = $this->create('    ');
        $this->assertFalse($docblock->isDefined());

        $docblock = $this->create('/** Hello */');
        $this->assertTrue($docblock->isDefined());
    }

    public function testRaw(): void
    {
        $docblock = $this->create('asd');
        $this->assertEquals('asd', $docblock->raw());
    }

    public function testReturnTypes(): void
    {
        $docblock = $this->create('/** @return Foo */');
        $this->assertEquals([ 'Foo' ], iterator_to_array($docblock->returnTypes()));
    }

    public function testMethodTypes(): void
    {
        $docblock = $this->create('/** @method Foo bar() */');
        $this->assertEquals([ 'Foo' ], iterator_to_array($docblock->methodTypes('bar')));
    }

    public function testVarTypes(): void
    {
        $docblock = $this->create('/** @var Foo $foo) */');
        $type = $docblock->vars()->types()->best();
        assert($type instanceof ClassType);
        $this->assertEquals('Foo', $type);
    }

    public function testArrayTypes(): void
    {
        $docblock = $this->create('/** @var Foo[] $foo) */');

        $type = $docblock->vars()->types()->best();
        assert($type instanceof ArrayType);
        $this->assertEquals('Foo', $type->valueType->name->full());
    }

    public function testCollectionTypes(): void
    {
        $docblock = $this->create('/** @var Foo<Item> $foo) */');
        $this->assertEquals('Foo<Item>', (string)$docblock->vars()->types()->best());
        $type = $docblock->vars()->types()->best();
        assert($type instanceof GenericClassType);

        $this->assertEquals('Item', $type->iterableValueType()->name->full());
    }

    public function testInherits(): void
    {
        $docblock = $this->create('/** Hello */');
        $this->assertFalse($docblock->inherits());
        $docblock = $this->create('/** {@inheritDoc} */');
        $this->assertTrue($docblock->inherits());
    }

    public function testDeprecated(): void
    {
        $docblock = $this->create('/** Hello */');
        $this->assertFalse($docblock->inherits());
        $docblock = $this->create('/** @deprecated */');
        $this->assertTrue($docblock->deprecation()->isDefined());
    }

    public function testDeprecatedMessage(): void
    {
        $docblock = $this->create('/** @deprecated Use foobar instead */');
        $this->assertEquals('Use foobar instead', $docblock->deprecation()->message());
    }

    private function create(string $docblock): DocBlock
    {
        $factory = new DocblockFactory(ReflectorBuilder::create()->build());
        return $factory->create($docblock);
    }
}
