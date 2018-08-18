<?php

namespace Phpactor\WorseReflection\Core\Virtual\Collection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;

class VirtualReflectionMethodCollection extends AbstractReflectionCollection implements ReflectionMethodCollection
{
    public static function fromReflectionMethods(array $reflectionMethods)
    {
        $methods = [];
        foreach ($reflectionMethods as $reflectionMethod) {
            $methods[$reflectionMethod->name()] = $reflectionMethod;
        }
        return new self($methods);
    }

    public function byVisibilities(array $visibilities)
    {
    }

    public function belongingTo(ClassName $class)
    {
    }

    public function atOffset(int $offset)
    {
    }
}
