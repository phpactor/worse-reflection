<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Docblock;
use Phpactor\WorseReflection\Core\SourceCode;

abstract class AbstractReflectionClass extends AbstractReflectedNode
{
    abstract public function name(): ClassName;

    abstract protected function methods(): ReflectionMethodCollection;

    abstract public function sourceCode(): SourceCode;

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

    public function docblock(): Docblock
    {
        return Docblock::fromNode($this->node());
    }
}
