<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Type;

final class ReflectedClassType extends ClassType
{
    private ClassReflector $reflector;

    public ClassName $name;

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
