<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;

final class ReflectedClassType extends ClassType
{
    public ClassName $name;

    private ClassReflector $reflector;

    public function __construct(ClassReflector $reflector, ClassName $name)
    {
        $this->name = $name;
        $this->reflector = $reflector;
    }

    public function __toString(): string
    {
        return $this->name->full();
    }

    public function toPhpString(): string
    {
        return $this->__toString();
    }
}
