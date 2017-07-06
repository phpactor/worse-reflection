<?php

namespace Phpactor\WorseReflection\Reflection;

use Microsoft\PhpParser\NamespacedNameInterface;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\ClassName;
use Phpactor\WorseReflection\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Reflection\Collection\ReflectionConstantCollection;

abstract class AbstractReflectionClass
{
    public function name(): ClassName
    {
        return ClassName::fromString((string) $this->node()->getNamespacedName());
    }

    abstract protected function constants(): ReflectionConstantCollection;

    abstract protected function node(): NamespacedNameInterface;

    abstract protected function reflector(): Reflector;

    abstract protected function methods(): ReflectionMethodCollection;
}
