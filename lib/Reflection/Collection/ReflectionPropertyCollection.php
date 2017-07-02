<?php

namespace DTL\WorseReflection\Reflection\Collection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\Reflection\ReflectionProperty;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Expression\Variable;

class ReflectionPropertyCollection extends AbstractReflectionCollection
{
    public static function fromClassDeclaration(Reflector $reflector, ClassDeclaration $class)
    {
        $properties = array_filter($class->classMembers->classMemberDeclarations, function ($member) {
            return $member instanceof PropertyDeclaration;
        });

        $items = [];
        foreach ($properties as $property) {
            foreach ($property->propertyElements as $propertyElement) {
                foreach ($propertyElement as $variable) {
                    if (false === $variable instanceof Variable) {
                        continue;
                    }
                    $items[$variable->getName()] = new ReflectionProperty($reflector, $property, $variable);
                }
            }
        }

        return new static($reflector, $items);
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
}
