<?php

namespace Phpactor\WorseReflection\Reflection\Inference;

final class Frame
{
    /**
     * @var Assignments
     */
    private $properties;

    /**
     * @var Assignments
     */
    private $locals;

    public function __construct(
        LocalAssignments $locals = null,
        PropretyAssignments $properties = null
    )
    {
        $this->properties = $properties ?: PropertyAssignments::create();
        $this->locals = $locals ?: LocalAssignments::create();
    }

    public function locals(): Assignments
    {
        return $this->locals;
    }

    public function properties(): Assignments
    {
        return $this->properties;
    }
}

