<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\Logger;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;

class MethodReturnTypeResolver
{
    /**
     * @var ReflectionMethod
     */
    private $method;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(ReflectionMethod $method, Logger $logger)
    {
        $this->method = $method;
        $this->logger = $logger;
    }

    public function resolve(): Types
    {
        $resolvedTypes = $this->getDocblockTypesFromClassOrMethod($this->method);

        if ($resolvedTypes->count()) {
            return $resolvedTypes;
        }

        if ($this->method->returnType()->isDefined()) {
            return Types::fromTypes([ $this->method->returnType() ]);
        }

        $resolvedTypes = $this->getTypesFromParentClass($this->method->class());

        if ($resolvedTypes->count()) {
            return $resolvedTypes;
        }

        return $this->getTypesFromInterfaces($this->method->class());
    }

    private function getDocblockTypesFromClassOrMethod(ReflectionMethod $method): Types
    {
        $classMethodOverrides = $method->class()->docblock()->methodTypes($method->name());
        if ($classMethodOverrides) {
            return $this->resolveTypes($classMethodOverrides);
        }

        return $this->resolveTypes($method->docblock()->returnTypes());
    }

    private function resolveTypes(array $types): Types
    {
        return Types::fromTypes(array_map(function (Type $type) {
            return $this->method->scope()->resolveFullyQualifiedName($type, $this->method->class());
        }, $types));
    }

    private function getTypesFromParentClass(ReflectionClassLike $reflectionClassLike): Types
    {
        if (false === $reflectionClassLike->isClass()) {
            return Types::empty();
        }

        if (null === $reflectionClassLike->parent()) {
            return Types::empty();
        }

        /** @var ReflectionClass $reflectioClass */
        $reflectionClass = $reflectionClassLike->parent();
        if (false === $reflectionClass->methods()->has($this->method->name())) {
            return Types::empty();
        }

        return $reflectionClass->methods()->get($this->method->name())->inferredReturnTypes();
    }

    private function getTypesFromInterfaces(ReflectionClassLike $reflectionClassLike): Types
    {
        if (false === $reflectionClassLike->isClass()) {
            return Types::empty();
        }

        /** @var ReflectionClass $reflectionClass */
        $reflectionClass = $reflectionClassLike;

        /** @var ReflectionInterface $interface */
        foreach ($reflectionClass->interfaces() as $interface) {
            if ($interface->methods()->has($this->method->name())) {
                return $interface->methods()->get($this->method->name())->inferredReturnTypes();
            }
        }

        return Types::empty();
    }
}
