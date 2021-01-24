<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Closure;

class Frame
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

    /**
     * @var string
     */
    private $name;

    public function __construct(
        string $name,
        LocalAssignments $locals = null,
        PropertyAssignments $properties = null,
        Problems $problems = null,
        Frame $parent = null
    ) {
        $this->properties = $properties ?: PropertyAssignments::create();
        $this->locals = $locals ?: LocalAssignments::create();
        $this->problems = $problems ?: Problems::create();
        $this->parent = $parent;
        $this->name = $name;
    }

    public function new(string $name): Frame
    {
        $frame = new self($name, null, null, null, $this);
        $this->children[] = $frame;

        return $frame;
    }

    /**
     * @return Assignments<Variable>
     */
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

    public function reduce(Closure $closure, $initial = null)
    {
        $initial = $closure($this, $initial);

        foreach ($this->children as $childFrame) {
            $initial = $childFrame->reduce($closure, $initial);
        }

        return $initial;
    }

    public function root()
    {
        if (null === $this->parent) {
            return $this;
        }

        return $this->parent->root();
    }

    public function children(): array
    {
        return $this->children;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function withLocals(LocalAssignments $locals)
    {
        $new = clone $this;
        $new->locals = $locals;

        return $new;
    }
}
