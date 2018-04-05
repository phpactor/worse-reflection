<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Name;
use InvalidArgumentException;

class NameTest extends TestCase
{
    public function testHead()
    {
        $name = Name::fromString('Foo\\Bar\\Baz');
        $this->assertEquals('Foo', (string) $name->head());
    }

    public function testTail()
    {
        $name = Name::fromString('Foo\\Bar\\Baz');
        $this->assertEquals('Bar\\Baz', (string) $name->tail());
    }

    public function testIsFullyQualified()
    {
        $name = Name::fromString('\\Foo\\Bar\\Baz');
        $this->assertTrue($name->wasFullyQualified());
    }

    public function testThrowsExceptionOnInvalidName()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid class name "Foo<BAR>"');

        Name::fromString('Foo<BAR>');
    }
}
