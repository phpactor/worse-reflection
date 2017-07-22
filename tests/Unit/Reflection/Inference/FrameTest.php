<?php

namespace Phpactor\WorseReflection\Tests\Unit\Reflection\Inference;

use Phpactor\WorseReflection\Reflection\Inference\Assignments;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Reflection\Inference\Frame;
use Phpactor\WorseReflection\Reflection\Inference\LocalAssignments;
use Phpactor\WorseReflection\Reflection\Inference\PropertyAssignments;

class FrameTest extends TestCase
{
    /**
     * @testdox It returns local and class assignments.
     */
    public function testAssignments()
    {
        $frame = new Frame();
        $this->assertInstanceOf(LocalAssignments::class, $frame->locals());
        $this->assertInstanceOf(PropertyAssignments::class, $frame->properties());
    }
}
