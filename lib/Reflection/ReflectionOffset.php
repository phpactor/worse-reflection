<?php

namespace DTL\WorseReflection\Reflection;

use PhpParser\Node;
use DTL\WorseReflection\Node\NodeAndAncestors;

class ReflectionOffset
{
    /**
     * @var Node
     */
    private $node;

    /**
     * @var ReflectionFrame
     */
    private $frame;

    public function __construct(NodeAndAncestors $node, ReflectionFrame $frame)
    {
        $this->node = $node;
        $this->frame = $frame;
    }

    public function hasNode(): bool
    {
        return null !== $this->node;
    }

    public function getNode(): NodeAndAncestors
    {
        return $this->node;
    }

    public function getFrame(): ReflectionFrame
    {
        return $this->frame;
    }
}
