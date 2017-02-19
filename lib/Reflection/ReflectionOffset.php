<?php

namespace DTL\WorseReflection\Reflection;

use PhpParser\Node;
use DTL\WorseReflection\Node\NodeAndAncestors;
use DTL\WorseReflection\TypeResolver;

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

    /**
     * @var TypeResolver
     */
    private $typeResolver;

    public function __construct(TypeResolver $typeResolver, NodeAndAncestors $node, ReflectionFrame $frame)
    {
        $this->node = $node;
        $this->frame = $frame;
        $this->typeResolver = $typeResolver;

    }

    public function hasNode(): bool
    {
        return null !== $this->node;
    }

    public function getNode(): NodeAndAncestors
    {
        return $this->node;
    }

    public function getType(): Type
    {
        return $this->typeResolver->resolveParserNode($this->frame, $this->node);
    }

    public function getFrame(): ReflectionFrame
    {
        return $this->frame;
    }
}
