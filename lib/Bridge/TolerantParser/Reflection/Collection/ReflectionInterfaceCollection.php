<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection;

use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\WorseReflection\Core\ClassName;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection as CoreReflectionInterfaceCollection;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionInterface get()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionInterface first()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionInterface last()
 */
class ReflectionInterfaceCollection extends AbstractReflectionCollection implements CoreReflectionInterfaceCollection
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
                $interface = $serviceLocator->reflector()->reflectInterface(
                    ClassName::fromString((string) $name->getResolvedName())
                );
                $items[$interface->name()->full()] = $interface;
            } catch (NotFound $e) {
            }
        }

        return new static($serviceLocator, $items);
    }

    protected function collectionType(): string
    {
        return CoreReflectionInterfaceCollection::class;
    }
}
