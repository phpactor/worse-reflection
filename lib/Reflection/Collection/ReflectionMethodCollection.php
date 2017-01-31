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
            array_filter($classNode->stmts, function ($stmt) {
                return $stmt instanceof ClassMethod;
            })
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
