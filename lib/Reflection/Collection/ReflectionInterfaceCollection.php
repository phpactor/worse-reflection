<?php

namespace Phpactor\WorseReflection\Reflection\Collection;

use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Reflection\ReflectionProperty;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\WorseReflection\ClassName;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;

class ReflectionInterfaceCollection extends AbstractReflectionCollection
{
    public static function fromInterfaceDeclaration(Reflector $reflector, InterfaceDeclaration $interface)
    {
        return self::fromBaseClause($reflector, $interface->interfaceBaseClause);
    }

    public static function fromClassDeclaration(Reflector $reflector, ClassDeclaration $class)
    {
        return self::fromBaseClause($reflector, $class->classInterfaceClause);
    }

    private static function fromBaseClause(Reflector $reflector, $baseClause)
    {
        if (null === $baseClause) {
            return new static($reflector, []);
        }

        $items = [];
        foreach ($baseClause->interfaceNameList->children as $name) {
            if (false === $name instanceof QualifiedName) {
                continue;
            }

            try {
                $interface = $reflector->reflectClass(
                    ClassName::fromString((string) $name->getResolvedName())
                );
                $items[$interface->name()->full()] = $interface;
            } catch (ClassNotFound $e) {
            }
        }

        return new static($reflector, $items);
    }
}
