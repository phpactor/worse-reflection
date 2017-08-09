<?php

namespace Phpactor\WorseReflection\Core\Reflection\Inference;

use Phpactor\WorseReflection\Core\Reflection\Inference\Assignments;
use Phpactor\WorseReflection\Core\Reflection\Inference\PropertyAssignments;

class PropertyAssignments extends Assignments
{
    public static function create()
    {
        return new self([]);
    }

    public static function fromArray(array $assignments): PropertyAssignments
    {
        return self::_fromArray($assignments);
    }
}
