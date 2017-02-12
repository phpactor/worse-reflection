<?php

namespace DTL\WorseReflection;

use PhpParser\Node;

class NodeReflector
{
    public function reflectNode(Reflector $reflector, SourceContext $sourceContext, Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            return new ReflectionClass($reflector, $sourceContext, $node);
        }

        if ($node instanceof Node\Const_) {
            return new ReflectionConstant($node->name, $node->value->value);
        }

        if ($node instanceof Node\Stmt\ClassMethod) {
            return new ReflectionMethod($reflector, $sourceContext, $node);
        }

        if ($node instanceof Node\Param) {
            return new ReflectionParameter($reflector, $sourceContext, $node);
        }

        if ($node instanceof Node\Stmt\PropertyProperty) {
            $ancestorProperty = $this->souceContext->ancestor($node, Property::class);
            return new RefletionProperty($ancestorProperty, $node);
        }
    }
}
