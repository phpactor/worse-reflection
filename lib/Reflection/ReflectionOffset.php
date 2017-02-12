<?php

namespace DTL\WorseReflection\Reflection;

use PhpParser\Node;
use DTL\WorseReflection\Node\LinearNodeTraverser;

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

    public function __construct(LinearNodeTraverser $node, ReflectionFrame $frame)
    {
        $this->node = $node;
        $this->frame = $frame;
    }

    public function hasNode(): bool
    {
        return null !== $this->node;
    }

    public function getNode(): LinearNodeTraverser
    {
        return $this->node;
    }

    public function getFrame(): ReflectionFrame
    {
        return $this->frame;
    }
}
