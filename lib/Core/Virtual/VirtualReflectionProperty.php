<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;

class VirtualReflectionProperty extends VirtualReflectionMember implements ReflectionProperty
{
    public function isVirtual(): bool
    {
        return true;
    }

    public function isStatic(): bool
    {
        return false;
    }
}
