<?php

namespace Phpactor\WorseReflection\Reflection;

use Phpactor\WorseReflection\Reflector;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\ConstElement;

final class ReflectionConstant
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

}
