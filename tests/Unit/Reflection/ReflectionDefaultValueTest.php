<?php

namespace Phpactor\WorseReflection\Tests\Unit\Reflection;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\DefaultValue;

class ReflectionDefaultValueTest extends TestCase
{
    /**
     * @testdox It creates an undefined default value.
     */
    public function testNone()
    {
        $value = DefaultValue::undefined();
        $this->assertFalse($value->isDefined());
    }

    /**
     * @testdox It represents a value
     */
    public function testValue()
    {
        $value = DefaultValue::fromValue(42);
        $this->assertEquals(42, $value->value());
    }
}
