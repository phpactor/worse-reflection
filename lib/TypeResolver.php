<?php

declare(strict_types=1);

namespace DTL\WorseReflection;

use PhpParser\Node;
use DTL\WorseReflection\Frame\Frame;
use DTL\WorseReflection\Reflection\ReflectionFrame;

class TypeResolver
{
    private $reflector;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function resolveParserNode(ReflectionFrame $frame, Node $node)
    {
        if ($node instanceof Node\Expr\MethodCall) {
            $subjectType = $this->resolveParserNode($frame, $node->var);
        }

        if ($node instanceof Node\Expr\Variable) {
            return $frame->getType($node->name);
        }

        if ($node instanceof Node\Expr\MethodCall) {
            $class = $this->reflector->reflectClass($subjectType->getClassName());
            return $class->getMethods()->get($node->name)->getReturnType();
        }
    }

    public function resolveNodeAndAncestors(NodeAndAncestors $nodeAndAncestors)
    {
    }
}
