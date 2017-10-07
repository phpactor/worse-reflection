<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Inference;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Inference\Assignments;
use Phpactor\WorseReflection\Core\Inference\SymbolInformation;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\WorseReflection\Core\Offset;

abstract class AssignmentstTestCase extends TestCase
{
    abstract protected function assignments(): Assignments;

    public function testAddByName()
    {
        $assignments = $this->assignments();
        $this->assertCount(0, $assignments->byName('hello'));

        $value = SymbolInformation::fromTypeAndValue(Type::fromString('Foobar'), 'goodbye');

        $assignments->add(Variable::fromOffsetNameAndValue(
            Offset::fromInt(0),
            'hello',
            SymbolInformation::fromTypeAndValue(Type::fromString('Foobar'), 'goodbye')
        ));

        $this->assertEquals($value, $assignments->byName('hello')->first()->symbolInformation());
    }

    public function testByName()
    {
        $assignments = $this->assignments();
        $assignments->add(Variable::fromOffsetNameAndValue(
            Offset::fromInt(0),
            'hello',
            SymbolInformation::fromTypeAndValue(Type::fromString('string'), 'goodbye')
        ));

        $assignments->add(Variable::fromOffsetNameAndValue(
            Offset::fromInt(10),
            'hello',
            SymbolInformation::fromTypeAndValue(Type::fromString('string'), 'hello')
        ));

        $this->assertCount(2, $assignments->byName('hello'));
    }
}
