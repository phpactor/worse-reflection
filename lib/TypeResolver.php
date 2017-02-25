<?php

declare(strict_types=1);

namespace DTL\WorseReflection;

use PhpParser\Node;
use DTL\WorseReflection\Frame\Frame;
use DTL\WorseReflection\Reflection\ReflectionFrame;
use DTL\WorseReflection\Type;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\SourceContext;

class TypeResolver
{
    private $reflector;
    private $sourceContext;

    public function __construct(Reflector $reflector, SourceContext $sourceContext)
    {
        $this->reflector = $reflector;
        $this->sourceContext = $sourceContext;
    }

    public function resolveParserNode(ReflectionFrame $frame, Node $node)
    {
        if ($node instanceof Node\Expr\MethodCall || $node instanceof Node\Expr\PropertyFetch) {
            $subjectType = $this->resolveParserNode($frame, $node->var);
        }

        if ($node instanceof Node\Expr\StaticCall) {
            return Type::class($this->sourceContext->resolve(ClassName::fromParts($node->class->parts)));
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
