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
use DTL\WorseReflection\Visibility;

class ReflectionPropertyCollection extends AbstractReflectionCollection
{
    public static function fromClassNode(SourceContext $sourceContext, ClassLike $classNode)
    {
        return new self(
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

    public function withoutPrivate()
    {
        return new self('property', array_filter($this->all(), function ($property) {
            return false === $property->getVisibility()->isPrivate();
        }));
    }

    public function merge(ReflectionPropertyCollection $collection)
    {
        return new self('property', array_merge(
            $this->all(),
            $collection->all()
        ));
    }
}

