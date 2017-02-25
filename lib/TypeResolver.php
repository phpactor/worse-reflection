<?php

declare(strict_types=1);

namespace DTL\WorseReflection;

use PhpParser\Node;
use DTL\WorseReflection\Frame\Frame;
use DTL\WorseReflection\Reflection\ReflectionFrame;
use DTL\WorseReflection\Type;

class TypeResolver
{
    private $reflector;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function resolveParserNode(ReflectionFrame $frame, Node $node)
    {
        if ($node instanceof Node\Expr\MethodCall || $node instanceof Node\Expr\PropertyFetch) {
            $subjectType = $this->resolveParserNode($frame, $node->var);
        }

        if ($node instanceof Node\Expr\Variable) {
            return $frame->getType($node->name);
        }

        if ($node instanceof Node\Expr\MethodCall) {
            $class = $this->reflector->reflectClass($subjectType->getClassName());
            return $class->getMethods()->get($node->name)->getReturnType();
        }

        if ($node instanceof Node\Expr\PropertyFetch) {
            $class = $this->reflector->reflectClass($subjectType->getClassName());
            $properties = $class->getProperties();

            if (!isset($properties[$node->name])) {
                return Type::unknown();
            }

            return $properties[$node->name]->getType();
        }

        return Type::unknown();
    }
}
