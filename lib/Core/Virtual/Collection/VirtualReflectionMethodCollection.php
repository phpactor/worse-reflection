<?php

namespace Phpactor\WorseReflection\Core\Virtual\Collection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;

class VirtualReflectionMethodCollection extends VirtualReflectionMemberCollection implements ReflectionMethodCollection
{
    public static function fromReflectionMethods(array $reflectionMethods): self
    {
        $methods = [];
        foreach ($reflectionMethods as $reflectionMethod) {
            $methods[$reflectionMethod->name()] = $reflectionMethod;
        }
        return new self($methods);
    }

    protected function collectionType(): string
    {
        return ReflectionMethodCollection::class;
    }
}
