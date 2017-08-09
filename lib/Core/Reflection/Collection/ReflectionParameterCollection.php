<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;

class ReflectionParameterCollection extends AbstractReflectionCollection
{
    public static function fromMethodDeclaration(ServiceLocator $serviceLocator, MethodDeclaration $method)
    {
        $items = [];

        if ($method->parameters) {
            foreach ($method->parameters->getElements() as $parameter) {
                $items[$parameter->getName()] = new ReflectionParameter($serviceLocator, $parameter);
            }
        }


        return new static($serviceLocator, $items);
    }
}
