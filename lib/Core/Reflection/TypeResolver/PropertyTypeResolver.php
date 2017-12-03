<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Types;
use Phpactor\WorseReflection\Core\Type;

class PropertyTypeResolver
{
    /**
     * @var ReflectionProperty
     */
    private $property;

    public function __construct(ReflectionProperty $property)
    {
        $this->property = $property;
    }

    public function resolve(): Types
    {
        $docblockTypes = $this->getDocblockTypes();

        $resolvedTypes = array_map(function (Type $type) {
            return $this->property->scope()->resolveFullyQualifiedName($type, $this->property->class());
        }, $docblockTypes);

        $resolvedTypes = Types::fromInferredTypes($resolvedTypes);

        if (false === $this->property->docblock()->inherits()) {
            return $resolvedTypes;
        }

        if ($this->isOverriding()) {
            $parentProperty = $this->property->class()->parent()->propertys()->get($this->property->name());

            return $resolvedTypes->merge($parentProperty->inferredReturnTypes());
        }

        $this->logger->warning(sprintf(
            'inheritdoc used on class "%s", but class has no parent',
            $this->property->class()->name()->full()
        ));

        return $resolvedTypes;
    }

    private function getDocblockTypes()
    {
        return $this->property->docblock()->varTypes();
    }

    private function isOverriding()
    {
        if (false === $this->property->class()->isClass()) {
            return false;
        }

        $parent = $this->property->class()->parent();

        return $parent && $parent->properties()->has($this->property->name());
    }
}
