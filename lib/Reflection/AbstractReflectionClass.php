<?php

namespace DTL\WorseReflection\Reflection;

use Microsoft\PhpParser\NamespacedNameInterface;
use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\ClassName;

abstract class AbstractReflectionClass
{
    public function name(): ClassName
    {
        return ClassName::fromString((string) $this->node()->getNamespacedName());
    }

    public function parent(): ReflectionClass
    {
        if (null === $this->baseClass()) {
            return;
        }
        
        return $this->reflector()->reflectClass(ClassName::fromString((string) $this->baseClass()->getResolvedName()));
    }

    /**
     * @return null|QualifiedName
     */
    abstract protected function baseClass();

    abstract protected function node(): NamespacedNameInterface;

    abstract protected function reflector(): Reflector;
}
