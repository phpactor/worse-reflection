<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Name;

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
}
