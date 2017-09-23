<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionProperty get()
 */
class ReflectionPropertyCollection extends AbstractReflectionCollection
{
    public static function fromClassDeclaration(ServiceLocator $serviceLocator, ClassDeclaration $class, ReflectionClass $reflectionClass)
    {
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
}
