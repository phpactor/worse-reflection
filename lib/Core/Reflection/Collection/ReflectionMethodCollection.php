<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\WorseReflection\Core\ClassName;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Core\Reflection\Collection\AbstractReflectionCollection;

class ReflectionMethodCollection extends AbstractReflectionCollection
{
    public static function fromClassDeclaration(ServiceLocator $serviceLocator, ClassDeclaration $class)
    {
        $methods = array_filter($class->classMembers->classMemberDeclarations, function ($member) {
            return $member instanceof MethodDeclaration;
        });

        $items = [];
        foreach ($methods as $method) {
            $items[$method->getName()] = new ReflectionMethod($serviceLocator, $method);
        }

        return new static($serviceLocator, $items);
    }

    public static function fromInterfaceDeclaration(ServiceLocator $serviceLocator, InterfaceDeclaration $interface)
    {
        $methods = array_filter($interface->interfaceMembers->interfaceMemberDeclarations, function ($member) {
            return $member instanceof MethodDeclaration;
        });

        $items = [];
        foreach ($methods as $method) {
            $items[$method->getName()] = new ReflectionMethod($serviceLocator, $method);
        }

        return new static($serviceLocator, $items);
    }

    public static function fromTraitDeclaration(ServiceLocator $serviceLocator, TraitDeclaration $trait)
    {
        $methods = array_filter($trait->traitMembers->traitMemberDeclarations, function ($member) {
            return $member instanceof MethodDeclaration;
        });

        $items = [];
        foreach ($methods as $method) {
            $items[$method->getName()] = new ReflectionMethod($serviceLocator, $method);
        }

        return new static($serviceLocator, $items);
    }

    public static function fromReflectionMethods(ServiceLocator $serviceLocator, array $methods)
    {
        return new static($serviceLocator, $methods);
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

        return new static($this->serviceLocator, $items);
    }

    public function belongingTo(ClassName $class)
    {
        return new self($this->serviceLocator, array_filter($this->items, function (ReflectionMethod $item) use ($class) {
            return $item->class()->name() == $class;
        }));
    }

    public function abstract()
    {
        return new self($this->serviceLocator, array_filter($this->items, function (ReflectionMethod $item) {
            return $item->isAbstract();
        }));
    }
}
