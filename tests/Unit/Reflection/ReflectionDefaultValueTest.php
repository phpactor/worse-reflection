<?php

namespace Phpactor\WorseReflection\Tests\Unit\Reflection;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Reflection\ReflectionDefaultValue;

class ReflectionDefaultValueTest extends TestCase
{
    /**
     * @testdox It returns none.
     */
    public function testNone()
    {
        $value = ReflectionDefaultValue::none();
        $this->assertTrue($value->isNone());
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
