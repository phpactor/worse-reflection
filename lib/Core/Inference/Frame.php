<?php

namespace Phpactor\WorseReflection\Core\Inference;

final class Frame
{
    /**
     * @var PropertyAssignments
     */
    private $properties;

    /**
     * @var LocalAssignments
     */
    private $locals;

    public function __construct(
        LocalAssignments $locals = null,
        PropertyAssignments $properties = null
    ) {
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
