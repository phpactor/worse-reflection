<?php

namespace DTL\WorseReflection\Reflection\Collection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContext;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use DTL\WorseReflection\Reflection\ReflectionMethod;
use PhpParser\Node;
use DTL\WorseReflection\Reflection\ReflectionClass;

class ReflectionMethodCollection extends AbstractReflectionCollection
{
    public static function generate(Reflector $reflector, ReflectionClass $declaringClass, SourceContext $sourceContext, ClassLike $classNode)
    {
        return new self(
            'method',
            array_reduce(array_filter($classNode->stmts, function ($stmt) {
                return $stmt instanceof ClassMethod;
            }), function ($methods, $node) use ($reflector, $sourceContext, $declaringClass) {
                $methods[$node->name] = new ReflectionMethod($reflector, $declaringClass, $sourceContext, $node);
                return $methods;
            }, [])
        );
    }
}
