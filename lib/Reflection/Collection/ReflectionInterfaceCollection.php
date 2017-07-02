<?php

namespace DTL\WorseReflection\Reflection\Collection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\Reflection\ReflectionProperty;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\QualifiedName;
use DTL\WorseReflection\ClassName;

class ReflectionInterfaceCollection extends AbstractReflectionCollection
{
    public static function fromClassDeclaration(Reflector $reflector, ClassDeclaration $class)
    {
        if (!$class->classInterfaceClause) {
            return new static($reflector, []);
        }

        $items = [];
        foreach ($class->classInterfaceClause->interfaceNameList->children as $name) {
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
