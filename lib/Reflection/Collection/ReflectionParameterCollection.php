<?php

namespace DTL\WorseReflection\Reflection\Collection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContext;
use PhpParser\Node\Stmt\ClassLike;
use DTL\WorseReflection\Reflection\ReflectionMethod;
use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use DTL\WorseReflection\Reflection\ReflectionParameter;

class ReflectionParameterCollection extends AbstractReflectionCollection
{
    public function __construct(Reflector $reflector, SourceContext $sourceContext, ClassMethod $methodNode)
    {
        parent::__construct(
            'parameter',
            $reflector,
            $sourceContext,
            array_filter($methodNode->params, function ($stmt) {
                return $stmt instanceof Param;
            })
        );
    }

    protected function createReflectionElement(Reflector $reflector, SourceContext $sourceContext, Node $node)
    {
        return new ReflectionParameter($reflector, $sourceContext, $node);
    }
}

