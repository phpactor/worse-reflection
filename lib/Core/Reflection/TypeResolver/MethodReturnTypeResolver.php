<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\Logger;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Type;

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
        if ($this->method->returnType()->isDefined()) {
            return Types::fromTypes([ $this->method->returnType() ]);
        }

        $docblockTypes = $this->getDocblockTypes();

        $resolvedTypes = array_map(function (Type $type) {
            return $this->method->scope()->resolveFullyQualifiedName($type, $this->method->class());
        }, $docblockTypes);

        $resolvedTypes = Types::fromTypes($resolvedTypes);

        if (false === $this->method->docblock()->inherits()) {
            return $resolvedTypes;
        }

        if ($this->isOverriding()) {
            $parentMethod = $this->method->class()->parent()->methods()->get($this->method->name());

            return $resolvedTypes->merge($parentMethod->inferredReturnTypes());
        }

        $this->logger->warning(sprintf(
            'inheritdoc used on class "%s", but class has no parent',
            $this->method->class()->name()->full()
        ));

        return $resolvedTypes;
    }

    private function getDocblockTypes()
    {
        $classMethodOverrides = $this->method->class()->docblock()->methodTypes();

        if (isset($classMethodOverrides[$this->method->name()])) {
            return [ $classMethodOverrides[$this->method->name()] ];
        }

        return $this->method->docblock()->returnTypes();
    }

    private function isOverriding()
    {
        if (
            false === $this->method->class()->isClass() &&
            false === $this->method->class()->isInterface()
        ) {
            return false;
        }

        $parent = $this->method->class()->parent();

        return $parent && $parent->methods()->has($this->method->name());
    }
}
