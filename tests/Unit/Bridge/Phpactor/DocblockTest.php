<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\Phpactor;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Bridge\Phpactor\DocblockFactory;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Type;

class DocblockTest extends TestCase
{
    public function testIsDefined()
    {
        $docblock = $this->create('');
        $this->assertFalse($docblock->isDefined());

        $docblock = $this->create('    ');
        $this->assertFalse($docblock->isDefined());

        $docblock = $this->create('/** Hello */');
        $this->assertTrue($docblock->isDefined());
    }

    public function testRaw()
    {
        $docblock = $this->create('asd');
        $this->assertEquals('asd', $docblock->raw());
    }

    public function testReturnTypes()
    {
        $docblock = $this->create('/** @return Foo */');
        $this->assertEquals([ 'Foo' ], iterator_to_array($docblock->returnTypes()));
    }

    public function testMethodTypes()
    {
        $docblock = $this->create('/** @method Foo bar() */');
        $this->assertEquals([ 'Foo' ], iterator_to_array($docblock->methodTypes('bar')));
    }

    public function testVarTypes()
    {
        $docblock = $this->create('/** @var Foo $foo) */');
        $this->assertEquals('Foo', $docblock->vars()->types()->best()->className()->full());
        $this->assertFalse($docblock->vars()->types()->best()->arrayType()->isDefined());
    }

    public function testArrayTypes()
    {
        $docblock = $this->create('/** @var Foo[] $foo) */');
        $this->assertTrue($docblock->vars()->types()->best()->arrayType()->isDefined());
        $this->assertEquals('Foo', $docblock->vars()->types()->best()->arrayType()->className()->full());
    }

    public function testCollectionTypes()
    {
        $docblock = $this->create('/** @var Foo<Item> $foo) */');
        $this->assertTrue($docblock->vars()->types()->best()->arrayType()->isDefined());
        $this->assertEquals('Foo', $docblock->vars()->types()->best()->short());
        $this->assertEquals('Item', $docblock->vars()->types()->best()->arrayType()->className()->full());
    }

    public function testInherits()
    {
        $docblock = $this->create('/** Hello */');
        $this->assertFalse($docblock->inherits());
        $docblock = $this->create('/** {@inheritDoc} */');
        $this->assertTrue($docblock->inherits());
    }

    private function create($docblock): DocBlock
    {
        $factory = new DocblockFactory();
        return $factory->create($docblock);
    }
}
