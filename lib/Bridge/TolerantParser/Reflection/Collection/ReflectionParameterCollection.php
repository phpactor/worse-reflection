<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection;

use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection as CoreReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;

/**
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionParameter get()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionParameter first()
 * @method \Phpactor\WorseReflection\Core\Reflection\ReflectionParameter last()
 */
class ReflectionParameterCollection extends AbstractReflectionCollection implements CoreReflectionParameterCollection
{
    public static function fromMethodDeclaration(ServiceLocator $serviceLocator, MethodDeclaration $method, ReflectionMethod $reflectionMethod)
    {
        $items = [];

        if ($method->parameters) {
            foreach ($method->parameters->getElements() as $parameter) {
                $items[$parameter->getName()] = new ReflectionParameter($serviceLocator, $reflectionMethod, $parameter);
            }
        }


        return new static($serviceLocator, $items);
    }
}
