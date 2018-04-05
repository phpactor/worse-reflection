<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\ClassName;

class ClassNameTest extends TestCase
{
    const CLASS_NAME = 'Foobar';

    public function testFromUnknownReturnsClassNameIfGivenClassName()
    {
        $givenClass = ClassName::fromString(self::CLASS_NAME);
        $className = ClassName::fromUnknown($givenClass);

        $this->assertSame($givenClass, $className);
    }

    public function testFromUnknownString()
    {
        $className = ClassName::fromUnknown(self::CLASS_NAME);

        $this->assertEquals(ClassName::fromString(self::CLASS_NAME), $className);
    }

    public function testFromUnknownInvalid()
    {
        $this->expectExceptionMessage('Do not know how to create class');
        ClassName::fromUnknown(new \stdClass);
    }

    public function testFromUnknownClassName()
    {
        $className1 = ClassName::fromString('Foobar');
        $className2 = ClassName::fromUnknown($className1);

        $this->assertSame($className1, $className2);
    }
}
