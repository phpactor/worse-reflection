<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Inference;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Inference\Assignments;
use Phpactor\WorseReflection\Core\Inference\SymbolInformation;
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

        $information = SymbolInformation::for(
            Symbol::fromTypeNameAndPosition(
                Symbol::VARIABLE, 'hello', Position::fromStartAndEnd(0, 0)
            )
        );

        $assignments->add(Variable::fromSymbolInformation($information));

        $this->assertEquals($information, $assignments->byName('hello')->first()->symbolInformation());
    }

    public function testMultipleByName()
    {
        $assignments = $this->assignments();

        $information = SymbolInformation::for(
            Symbol::fromTypeNameAndPosition(
                Symbol::VARIABLE, 'hello', Position::fromStartAndEnd(0, 0)
            )
        );

        $assignments->add(Variable::fromSymbolInformation($information));
        $assignments->add(Variable::fromSymbolInformation($information));

        $this->assertCount(2, $assignments->byName('hello'));
    }
}
