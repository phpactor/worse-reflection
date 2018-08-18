<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection;

use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionProperty;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionTrait;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection as CoreReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\ClassName;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionProperty get()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionProperty first()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionProperty last()
 */
class ReflectionPropertyCollection extends ReflectionMemberCollection implements corereflectionpropertycollection
{
    public static function fromClassDeclaration(ServiceLocator $serviceLocator, ClassDeclaration $class, ReflectionClass $reflectionClass)
    {
        /** @var PropertyDeclaration[] $properties */
        $properties = array_filter($class->classMembers->classMemberDeclarations, function ($member) {
            return $member instanceof PropertyDeclaration;
        });

        $items = [];
        foreach ($properties as $property) {
            foreach ($property->propertyElements as $propertyElement) {
                foreach ($propertyElement as $variable) {
                    if ($variable instanceof AssignmentExpression) {
                        $variable = $variable->leftOperand;
                    }

                    if (false === $variable instanceof Variable) {
                        continue;
                    }
                    $items[$variable->getName()] = new ReflectionProperty($serviceLocator, $reflectionClass, $property, $variable);
                }
            }
        }

        return new static($serviceLocator, $items);
    }

    public static function fromTraitDeclaration(ServiceLocator $serviceLocator, TraitDeclaration $trait, ReflectionTrait $reflectionTrait)
    {
        /** @var PropertyDeclaration[] $properties */
        $properties = array_filter($trait->traitMembers->traitMemberDeclarations, function ($member) {
            return $member instanceof PropertyDeclaration;
        });

        $items = [];
        foreach ($properties as $property) {
            foreach ($property->propertyElements as $propertyElement) {
                foreach ($propertyElement as $variable) {
                    if (false === $variable instanceof Variable) {
                        continue;
                    }
                    $items[$variable->getName()] = new ReflectionProperty($serviceLocator, $reflectionTrait, $property, $variable);
                }
            }
        }

        return new static($serviceLocator, $items);
    }

    protected function collectionType(): string
    {
        return CoreReflectionPropertyCollection::class;
    }
}
