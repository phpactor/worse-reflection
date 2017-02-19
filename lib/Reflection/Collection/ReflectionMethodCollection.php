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
            $reflector,
            $sourceContext,
            array_reduce(array_filter($classNode->stmts, function ($stmt) {
                return $stmt instanceof ClassMethod;
            }), function ($methods, $node) {
                $methods[$node->name] = $node;
                return $methods;
            }, [])
        );
    }

    protected function createReflectionElement(Reflector $reflector, SourceContext $sourceContext, Node $node)
    {
        return new ReflectionMethod($reflector, $sourceContext, $node);
    }

    public function get(string $name): ReflectionMethod
    {
        return parent::get($name);
    }
}
