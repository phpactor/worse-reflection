<?php

namespace DTL\WorseReflection\Reflection\Collection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContext;
use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use DTL\WorseReflection\Reflection\ReflectionProperty;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\ClassLike;

class ReflectionPropertyCollection extends AbstractReflectionCollection
{
    public function __construct(SourceContext $sourceContext, ClassLike $classNode)
    {
        parent::__construct(
            'property',
            array_reduce(array_filter($classNode->stmts, function ($stmt) {
                return $stmt instanceof Property;
            }), function ($properties, $propertyNode) use ($sourceContext) {

                foreach ($propertyNode->props as $realProperty) {
                    $properties[$realProperty->name] = new ReflectionProperty($sourceContext, $propertyNode, $realProperty);
                }

                return $properties;
            }, [])
        );
    }
}

