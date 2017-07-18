<?php

namespace Phpactor\WorseReflection\Tests\Unit\Reflection;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Reflection\ReflectionDefaultValue;

class ReflectionDefaultValueTest extends TestCase
{
    /**
     * @testdox It creates an undefined default value.
     */
    public function testNone()
    {
        $value = ReflectionDefaultValue::undefined();
        $this->assertFalse($value->isDefined());
    }

    /**
     * @testdox It represents a value
     */
    public function testValue()
    {
        $value = ReflectionDefaultValue::fromValue(42);
        $this->assertEquals(42, $value->value());
    }
}
