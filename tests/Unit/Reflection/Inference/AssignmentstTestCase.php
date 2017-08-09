<?php

namespace Phpactor\WorseReflection\Tests\Unit\Reflection\Inference;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\Inference\Assignments;
use Phpactor\WorseReflection\Core\Reflection\Inference\Value;
use Phpactor\WorseReflection\Core\Reflection\Inference\Variable;
use Phpactor\WorseReflection\Core\Offset;

abstract class AssignmentstTestCase extends TestCase
{
    abstract protected function assignments(): Assignments;

    public function testAddByName()
    {
        $assignments = $this->assignments();
        $this->assertCount(0, $assignments->byName('hello'));

        $value = Value::fromTypeAndValue(Type::fromString('Foobar'), 'goodbye');

        $assignments->add(Variable::fromOffsetNameAndValue(
            Offset::fromInt(0),
            'hello',
            Value::fromTypeAndValue(Type::fromString('Foobar'), 'goodbye')
        ));

        $this->assertEquals($value, $assignments->byName('hello')->first()->value());
    }

    public function testByName()
    {
        $assignments = $this->assignments();
        $assignments->add(Variable::fromOffsetNameAndValue(
            Offset::fromInt(0),
            'hello',
            Value::fromTypeAndValue(Type::fromString('string'), 'goodbye')
        ));

        $assignments->add(Variable::fromOffsetNameAndValue(
            Offset::fromInt(10),
            'hello',
            Value::fromTypeAndValue(Type::fromString('string'), 'hello')
        ));

        $this->assertCount(2, $assignments->byName('hello'));
    }
}
