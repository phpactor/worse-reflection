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

    /**
     * @var Problems
     */
    private $problems;

    public function __construct(
        LocalAssignments $locals = null,
        PropertyAssignments $properties = null,
        Problems $problems = null
    ) {
        $this->properties = $properties ?: PropertyAssignments::create();
        $this->locals = $locals ?: LocalAssignments::create();
        $this->problems = $problems ?: Problems::create();
    }

    public function locals(): Assignments
    {
        return $this->locals;
    }

    public function properties(): Assignments
    {
        return $this->properties;
    }

    public function problems(): Problems
    {
        return $this->problems;
    }
}
