<?php

namespace Phpactor\WorseReflection\Reflection\Inference;

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
