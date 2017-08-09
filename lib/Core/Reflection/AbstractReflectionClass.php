<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Microsoft\PhpParser\NamespacedNameInterface;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\AbstractReflectedNode;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;

abstract class AbstractReflectionClass extends AbstractReflectedNode
{
    abstract public function name(): ClassName;

    abstract protected function methods(): ReflectionMethodCollection;

    public function isInterface()
    {
        return $this instanceof ReflectionInterface;
    }

    public function isTrait()
    {
        return $this instanceof ReflectionTrait;
    }

    public function isConcrete()
    {
        if ($this instanceof ReflectionInterface) {
            return false;
        }

        return false === $this->isAbstract();
    }
}
