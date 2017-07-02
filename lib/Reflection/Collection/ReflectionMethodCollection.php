<?php

namespace DTL\WorseReflection\Reflection\Collection;

use Microsoft\PhpParser\Node;
use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\Reflection\ReflectionMethod;

class ReflectionMethodCollection extends AbstractReflectionCollection
{
    protected function add(Node $method)
    {
        $this->items[$method->getName()] = $method;
    }
    
    protected function createFromNode(Reflector $reflector, Node $node)
    {
        return new ReflectionMethod($reflector, $node);
    }
}
