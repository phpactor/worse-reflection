<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Inference;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Inference\Assignments;
use Phpactor\WorseReflection\Core\Inference\SymbolContext;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Position;

abstract class AssignmentstTestCase extends TestCase
{
    abstract protected function assignments(): Assignments;

    public function testAddByName()
    {
        $assignments = $this->assignments();
        $this->assertCount(0, $assignments->byName('hello'));

        $information = SymbolContext::for(
            Symbol::fromTypeNameAndPosition(
                Symbol::VARIABLE, 'hello', Position::fromStartAndEnd(0, 0)
            )
        );

        $assignments->add(Variable::fromSymbolContext($information));

        $this->assertEquals($information, $assignments->byName('hello')->first()->symbolInformation());
    }

    public function testMultipleByName()
    {
        $assignments = $this->assignments();

        $information = SymbolContext::for(
            Symbol::fromTypeNameAndPosition(
                Symbol::VARIABLE, 'hello', Position::fromStartAndEnd(0, 0)
            )
        );

        $assignments->add(Variable::fromSymbolContext($information));
        $assignments->add(Variable::fromSymbolContext($information));

        $this->assertCount(2, $assignments->byName('hello'));
    }
}
