<?php

namespace DTL\WorseReflection\Reflection\Collection;

use Microsoft\PhpParser\Node;
use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\Reflection\ReflectionMethod;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\MethodDeclaration;

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
