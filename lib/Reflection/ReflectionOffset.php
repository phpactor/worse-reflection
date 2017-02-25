<?php

namespace DTL\WorseReflection\Reflection;

use PhpParser\Node;
use DTL\WorseReflection\Node\NodePath;
use DTL\WorseReflection\TypeResolver;
use DTL\WorseReflection\Type;

class ReflectionOffset
{
    /**
     * @var Node
     */
    private $nodePath;

    /**
     * @var ReflectionFrame
     */
    private $frame;

    /**
     * @var TypeResolver
     */
    private $typeResolver;

    public function __construct(TypeResolver $typeResolver, NodePath $nodePath, ReflectionFrame $frame)
    {
        $this->nodePath = $nodePath;
        $this->frame = $frame;
        $this->typeResolver = $typeResolver;

    }

    public function hasNode(): bool
    {
        return null !== $this->nodePath;
    }

    public function getNode(): NodePath
    {
        return $this->nodePath;
    }

    public function getType(): Type
    {
        return $this->typeResolver->resolveParserNode($this->frame, $this->nodePath->top());
    }

    public function getFrame(): ReflectionFrame
    {
        return $this->frame;
    }
}
