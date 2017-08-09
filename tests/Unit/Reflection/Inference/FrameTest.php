<?php

namespace Phpactor\WorseReflection\Tests\Unit\Reflection\Inference;

use Phpactor\WorseReflection\Core\Reflection\Inference\Assignments;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Reflection\Inference\Frame;
use Phpactor\WorseReflection\Core\Reflection\Inference\LocalAssignments;
use Phpactor\WorseReflection\Core\Reflection\Inference\PropertyAssignments;

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
