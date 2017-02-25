<?php

namespace DTL\WorseReflection\Reflection\Collection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContext;
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
            array_reduce(array_filter($methodNode->params, function ($stmt) {
                return $stmt instanceof Param;
            }), function ($params, $paramNode) use ($reflector, $sourceContext) {
                $params[$paramNode->name] = new ReflectionParameter($reflector, $sourceContext, $paramNode);
                return $params;
            }, [])
        );
    }
}

