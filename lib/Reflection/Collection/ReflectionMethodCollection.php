<?php

namespace Phpactor\WorseReflection\Reflection\Collection;

use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Reflection\ReflectionMethod;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\WorseReflection\ClassName;

class ReflectionMethodCollection extends AbstractReflectionCollection
{
    public static function fromClassDeclaration(Reflector $reflector, ClassDeclaration $class)
    {
        $methods = array_filter($class->classMembers->classMemberDeclarations, function ($member) {
            return $member instanceof MethodDeclaration;
        });

        $items = [];
        foreach ($methods as $method) {
            $items[$method->getName()] = new ReflectionMethod($reflector, $method);
        }

        return new static($reflector, $items);
    }

    public static function fromInterfaceDeclaration(Reflector $reflector, InterfaceDeclaration $interface)
    {
        $methods = array_filter($interface->interfaceMembers->interfaceMemberDeclarations, function ($member) {
            return $member instanceof MethodDeclaration;
        });

        $items = [];
        foreach ($methods as $method) {
            $items[$method->getName()] = new ReflectionMethod($reflector, $method);
        }

        return new static($reflector, $items);
    }

    public static function fromReflectionMethods(Reflector $reflector, array $methods)
    {
        return new static($reflector, $methods);
    }

    public function byVisibilities(array $visibilities)
    {
        $items = [];
        foreach ($this->items as $key => $item) {
            foreach ($visibilities as $visibility) {
                if ($item->visibility() != $visibility) {
                    continue;
                }

                $items[$key] = $item;
            }
        }

        return new static($this->reflector, $items);
    }

    public function belongingTo(ClassName $class)
    {
        return new self($this->reflector, array_filter($this->items, function (ReflectionMethod $item) use ($class) {
            return $item->class()->name() == $class;
        }));
    }
}
