<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Docblock;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope as CoreReflectionScope;

abstract class AbstractReflectionClass extends AbstractReflectedNode
{
    public function scope(): CoreReflectionScope
    {
        return new ReflectionScope($this->node());
    }

    public function isInterface(): bool
    {
        return $this instanceof ReflectionInterface;
    }

    public function isTrait(): bool
    {
        return $this instanceof ReflectionTrait;
    }

    public function isClass(): bool
    {
        return $this instanceof ReflectionClass;
    }

    public function isConcrete(): bool
    {
        return false;
    }

    public function docblock(): Docblock
    {
        return Docblock::fromNode($this->node());
    }
}
