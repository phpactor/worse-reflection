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

    abstract protected function node(): NamespacedNameInterface;

    abstract protected function reflector(): Reflector;
}
