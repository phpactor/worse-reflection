<?php

declare(strict_types=1);

namespace DTL\WorseReflection;

use PhpParser\Node;
use DTL\WorseReflection\Frame\Frame;
use DTL\WorseReflection\Reflection\ReflectionFrame;

class ChainResolver
{
    private $reflector;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function resolve(ReflectionFrame $frame, Node $node)
    {
        if ($node instanceof Node\Expr\MethodCall) {
            $subjectType = $this->resolve($frame, $node->var);
        }

        if ($node instanceof Node\Expr\Variable) {
            return $frame->get($node->name);
        }

        if ($node instanceof Node\Expr\MethodCall) {
            $class = $this->reflector->reflectClass($subjectType->getClassName());
            $method = $class->getMethods()->get($node->name);

            return $method->getReturnType();
        }
    }
}
