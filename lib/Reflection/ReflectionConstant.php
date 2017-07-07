<?php

namespace Phpactor\WorseReflection\Reflection;

use Phpactor\WorseReflection\Reflector;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\ConstElement;
use Microsoft\PhpParser\Node;

final class ReflectionConstant extends AbstractReflectedNode
{
    private $reflector;
    private $node;

    public function __construct(
        Reflector $reflector,
        ConstElement $node
    )
    {
        $this->reflector = $reflector;
        $this->node = $node;
    }
    public function name()
    {
        return $this->node->getName();
    }

    protected function node(): Node
    {
        return $this->node;
    }
}
