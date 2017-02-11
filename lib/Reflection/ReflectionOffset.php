<?php

namespace DTL\WorseReflection\Reflection;

use PhpParser\Node;
use DTL\WorseReflection\Reflection\ReflectionFrame;

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

    public function __construct(Node $node, ReflectionFrame $frame)
    {
        $this->node = $node;
        $this->frame = $frame;
    }

    public function hasNode(): bool
    {
        return null !== $this->node;
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function getFrame(): ReflectionFrame
    {
        return $this->frame;
    }
}
