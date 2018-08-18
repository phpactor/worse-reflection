<?php

namespace Phpactor\WorseReflection\Core\Virtual\Collection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;

abstract class VirtualReflectionMemberCollection extends AbstractReflectionCollection implements ReflectionMemberCollection
{
    public static function fromReflectionMethods(array $reflectionMethods)
    {
        $methods = [];
        foreach ($reflectionMethods as $reflectionMethod) {
            $methods[$reflectionMethod->name()] = $reflectionMethod;
        }
        return new static($methods);
    }


    public function byName(string $name): ReflectionMemberCollection
    {
    }

    public function byVisibilities(array $visibilities): ReflectionMemberCollection
    {
    }

    public function belongingTo(ClassName $class): ReflectionMemberCollection
    {
    }

    public function atOffset(int $offset): ReflectionMemberCollection
    {
    }
}
