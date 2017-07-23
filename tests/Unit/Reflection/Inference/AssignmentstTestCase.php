<?php

namespace Phpactor\WorseReflection\Tests\Unit\Reflection\Inference;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Type;
use Phpactor\WorseReflection\Reflection\Inference\Assignments;
use Phpactor\WorseReflection\Reflection\Inference\Value;
use Phpactor\WorseReflection\Reflection\Inference\Variable;

abstract class AssignmentstTestCase extends TestCase
{
    abstract protected function assignments(): Assignments;

    public function testAddByName()
    {
        $assignments = $this->assignments();
        $this->assertCount(0, $assignments->byName('hello'));

        $value = Value::fromTypeAndValue(Type::fromString('Foobar'), 'goodbye');

        $assignments->add(Variable::fromOffsetNameTypeAndValue(0, 'hello', 'Foobar', 'goodbye'));

        $this->assertEquals($value, $assignments->byName('hello')->first()->value());
    }

    public function testByName()
    {
        $assignments = $this->assignments();
        $assignments->add(Variable::fromOffsetNameTypeAndValue(0, 'hello', 'string', 'goodbye'));
        $assignments->add(Variable::fromOffsetNameTypeAndValue(10, 'hello', 'string', 'goodbye'));

        $this->assertCount(2, $assignments->byName('hello'));
    }
}
