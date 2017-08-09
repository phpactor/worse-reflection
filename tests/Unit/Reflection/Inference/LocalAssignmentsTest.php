<?php

namespace Phpactor\WorseReflection\Tests\Unit\Reflection\Inference;

use Phpactor\WorseReflection\Core\Reflection\Inference\Assignments;
use Phpactor\WorseReflection\Core\Reflection\Inference\LocalAssignments;

class LocalAssignmentsTest extends AssignmentstTestCase
{
    protected function assignments(): Assignments
    {
        return LocalAssignments::create();
    }
}
