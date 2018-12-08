<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Inference;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Inference\Assignments;
use Phpactor\WorseReflection\Core\Inference\SymbolContext;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Position;
use RuntimeException;

abstract class AssignmentstTestCase extends TestCase
{
    abstract protected function assignments(): Assignments;

    public function testAddByName()
    {
        $assignments = $this->assignments();
        $this->assertCount(0, $assignments->byName('hello'));

        $information = SymbolContext::for(
            Symbol::fromTypeNameAndPosition(
                Symbol::VARIABLE,
                'hello',
                Position::fromStartAndEnd(0, 0)
            )
        );

        $assignments->add(Variable::fromSymbolContext($information));

        $this->assertEquals($information, $assignments->byName('hello')->first()->symbolContext());
    }

    public function testMultipleByName()
    {
        $assignments = $this->assignments();

        $assignments->add($this->createVariable('hello', 0, 0));
        $assignments->add($this->createVariable('hello', 0, 0));

        $this->assertCount(2, $assignments->byName('hello'));
    }

    public function testLessThanEqualTo()
    {
        $assignments = $this->assignments();

        $assignments->add($this->createVariable('hello', 0, 5));
        $assignments->add($this->createVariable('hello', 5, 10));
        $assignments->add($this->createVariable('hello', 10, 15));

        $this->assertCount(2, $assignments->byName('hello')->lessThanOrEqualTo(5));
    }

    public function testLessThan()
    {
        $assignments = $this->assignments();

        $assignments->add($this->createVariable('hello', 0, 5));
        $assignments->add($this->createVariable('hello', 5, 10));
        $assignments->add($this->createVariable('hello', 10, 15));

        $this->assertCount(1, $assignments->byName('hello')->lessThan(5));
    }

    public function testGreaterThanOrEqualTo()
    {
        $assignments = $this->assignments();

        $assignments->add($this->createVariable('hello', 0, 5));
        $assignments->add($this->createVariable('hello', 5, 10));
        $assignments->add($this->createVariable('hello', 10, 15));

        $this->assertCount(2, $assignments->byName('hello')->greaterThanOrEqualTo(5));
    }

    public function testGreaterThan()
    {
        $assignments = $this->assignments();

        $assignments->add($this->createVariable('hello', 0, 5));
        $assignments->add($this->createVariable('hello', 5, 10));
        $assignments->add($this->createVariable('hello', 10, 15));

        $this->assertCount(1, $assignments->byName('hello')->greaterThan(5));
    }

    public function testThrowsExceptionIfIndexNotExist()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No variable at index "5"');
        $assignments = $this->assignments();

        $assignments->add($this->createVariable('hello', 0, 5));

        $this->assertCount(1, $assignments->atIndex(5));
    }

    private function createVariable(string $name, int $start, int $end): Variable
    {
        return Variable::fromSymbolContext(SymbolContext::for(Symbol::fromTypeNameAndPosition(
            Symbol::VARIABLE,
            $name,
            Position::fromStartAndEnd($start, $end)
        )));
    }
}
