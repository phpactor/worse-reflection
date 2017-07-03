<?php

namespace DTL\WorseReflection\Reflection\Collection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\Reflection\ReflectionProperty;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\MethodDeclaration;
use DTL\WorseReflection\Reflection\ReflectionParameter;

class ReflectionParameterCollection extends AbstractReflectionCollection
{
    public static function fromMethodDeclaration(Reflector $reflector, MethodDeclaration $method)
    {
        $items = [];

        if ($method->parameters) {
            foreach ($method->parameters->getElements() as $parameter) {
                $items[$parameter->getName()] = new ReflectionParameter($reflector, $parameter);
            }
        }


        return new static($reflector, $items);
    }
}
