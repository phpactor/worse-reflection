<?php

namespace Phpactor\WorseReflection\Reflection\Collection;

use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Reflection\ReflectionProperty;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Phpactor\WorseReflection\Reflection\ReflectionParameter;

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
