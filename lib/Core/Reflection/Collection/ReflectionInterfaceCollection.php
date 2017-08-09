<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\WorseReflection\Core\ClassName;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;

class ReflectionInterfaceCollection extends AbstractReflectionCollection
{
    public static function fromInterfaceDeclaration(ServiceLocator $serviceLocator, InterfaceDeclaration $interface)
    {
        return self::fromBaseClause($serviceLocator, $interface->interfaceBaseClause);
    }

    public static function fromClassDeclaration(ServiceLocator $serviceLocator, ClassDeclaration $class)
    {
        return self::fromBaseClause($serviceLocator, $class->classInterfaceClause);
    }

    private static function fromBaseClause(ServiceLocator $serviceLocator, $baseClause)
    {
        if (null === $baseClause) {
            return new static($serviceLocator, []);
        }

        $items = [];
        foreach ($baseClause->interfaceNameList->children as $name) {
            if (false === $name instanceof QualifiedName) {
                continue;
            }

            try {
                $interface = $serviceLocator->reflector()->reflectClass(
                    ClassName::fromString((string) $name->getResolvedName())
                );
                $items[$interface->name()->full()] = $interface;
            } catch (ClassNotFound $e) {
            }
        }

        return new static($serviceLocator, $items);
    }
}
