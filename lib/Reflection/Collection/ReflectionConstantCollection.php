<?php

namespace Phpactor\WorseReflection\Reflection\Collection;

use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Reflection\ReflectionProperty;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Phpactor\WorseReflection\Reflection\ReflectionConstant;

class ReflectionConstantCollection extends AbstractReflectionCollection
{
    public static function fromClassDeclaration(Reflector $reflector, ClassDeclaration $class)
    {
        $items = [];
        foreach ($class->classMembers->classMemberDeclarations as $member) {
            if (!$member instanceof ClassConstDeclaration) {
                continue;
            }

            foreach ($member->constElements->children as $constElement) {
                $items[$constElement->getName()] = new ReflectionConstant($reflector, $constElement);
            }
        }

        return new static($reflector, $items);
    }

    public static function fromReflectionConstants(Reflector $reflector, array $constants)
    {
        return new static($reflector, $constants);
    }


    public static function fromInterfaceDeclaration(Reflector $reflector, InterfaceDeclaration $interface)
    {
        $items = [];
        foreach ($interface->interfaceMembers->interfaceMemberDeclarations as $member) {
            if (!$member instanceof ClassConstDeclaration) {
                continue;
            }

            foreach ($member->constElements->children as $constElement) {
                $items[$constElement->getName()] = new ReflectionConstant($reflector, $constElement);
            }
        }
        return new static($reflector, $items);
    }
}
