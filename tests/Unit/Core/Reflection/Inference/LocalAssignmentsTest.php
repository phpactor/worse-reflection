<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Reflection\Inference;

use Phpactor\WorseReflection\Core\Reflection\Inference\Assignments;
use Phpactor\WorseReflection\Core\Reflection\Inference\LocalAssignments;
use Phpactor\WorseReflection\Tests\Unit\Core\Reflection\Inference\AssignmentstTestCase;

class LocalAssignmentsTest extends AssignmentstTestCase
{
    protected function assignments(): Assignments
    {
        return LocalAssignments::create();
    }
}
