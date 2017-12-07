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

    /**
     * @var Frame
     */
    private $parent;

    /**
     * @var Frame[]
     */
    private $children = [];

    public function __construct(
        LocalAssignments $locals = null,
        PropertyAssignments $properties = null,
        Problems $problems = null,
        Frame $parent = null
    ) {
        $this->properties = $properties ?: PropertyAssignments::create();
        $this->locals = $locals ?: LocalAssignments::create();
        $this->problems = $problems ?: Problems::create();
        $this->parent = $parent;
    }

    public function new(): Frame
    {
        $frame = new self(null, null, null, $this);
        $this->children[] = $frame;

        return $frame;
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

    public function parent(): Frame
    {
        return $this->parent;
    }
}
