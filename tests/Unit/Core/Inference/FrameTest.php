<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Inference;

use Phpactor\WorseReflection\Core\Inference\Assignments;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\LocalAssignments;
use Phpactor\WorseReflection\Core\Inference\PropertyAssignments;
use Phpactor\WorseReflection\Core\Inference\SymbolInformation;
use Phpactor\WorseReflection\Core\Inference\Problems;

class FrameTest extends TestCase
{
    /**
     * @testdox It returns local and class assignments.
     */
    public function testAssignments()
    {
        $frame = new Frame('test');
        $this->assertInstanceOf(LocalAssignments::class, $frame->locals());
        $this->assertInstanceOf(PropertyAssignments::class, $frame->properties());
    }

    public function testReduce()
    {
        $s1 = SymbolInformation::none();
        $s2 = SymbolInformation::none();

        $frame = new Frame('test');
        $frame->problems()->add($s1);

        $child = $frame->new('child');
        $child->problems()->add($s2);

        $problems = $frame->reduce(function (Frame $frame, Problems $problems) {
            return $problems->merge($frame->problems());
        }, Problems::create());

        $this->assertEquals([ $s1, $s2 ], $problems->toArray());
    }
}
