<?php

namespace Phpactor\WorseReflection\Reflection;

use Microsoft\PhpParser\NamespacedNameInterface;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\ClassName;
use Phpactor\WorseReflection\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Reflection\Collection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Reflection\AbstractReflectedNode;
use Phpactor\WorseReflection\Reflection\ReflectionInterface;

abstract class AbstractReflectionClass extends AbstractReflectedNode
{
    abstract public function name(): ClassName;

    abstract protected function constants(): ReflectionConstantCollection;

    abstract protected function reflector(): Reflector;

    abstract protected function methods(): ReflectionMethodCollection;

    public function isInterface()
    {
        return $this instanceof ReflectionInterface;
    }

    public function isConcrete()
    {
        if ($this instanceof ReflectionInterface) {
            return false;
        }

        return false === $this->isAbstract();
    }
}
