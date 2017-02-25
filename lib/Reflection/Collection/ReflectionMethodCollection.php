<?php

namespace DTL\WorseReflection\Reflection\Collection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContext;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use DTL\WorseReflection\Reflection\ReflectionMethod;
use PhpParser\Node;

class ReflectionMethodCollection extends AbstractReflectionCollection
{
    public function __construct(Reflector $reflector, SourceContext $sourceContext, ClassLike $classNode)
    {
        parent::__construct(
            'method',
            array_reduce(array_filter($classNode->stmts, function ($stmt) {
                return $stmt instanceof ClassMethod;
            }), function ($methods, $node) use ($reflector, $sourceContext) {
                $methods[$node->name] = new ReflectionMethod($reflector, $sourceContext, $node);
                return $methods;
            }, [])
        );
    }
}
