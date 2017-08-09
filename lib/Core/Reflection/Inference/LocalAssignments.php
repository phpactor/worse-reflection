<?php

namespace Phpactor\WorseReflection\Core\Reflection\Inference;

use Phpactor\WorseReflection\Core\Reflection\Inference\Assignments;
use Phpactor\WorseReflection\Core\Reflection\Inference\LocalAssignments;

final class LocalAssignments extends Assignments
{
    public static function create()
    {
        return new self([]);
    }

    public static function fromArray(array $assignments): LocalAssignments
    {
        return new self($assignments);
    }

}
