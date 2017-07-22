<?php

namespace Phpactor\WorseReflection\Tests\Unit\Reflection\Inference;

use Phpactor\WorseReflection\Reflection\Inference\Assignments;
use Phpactor\WorseReflection\Reflection\Inference\LocalAssignments;

class LocalAssignmentsTest extends AssignmentstTestCase
{
    protected function assignments(): Assignments
    {
        return LocalAssignments::create();
    }
}
