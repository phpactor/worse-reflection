<?php

namespace Phpactor\WorseReflection\Reflection;

use Phpactor\WorseReflection\Reflector;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\ConstElement;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Type;
use Phpactor\WorseReflection\ServiceLocator;
use Phpactor\WorseReflection\Reflection\Inference\Frame;

final class ReflectionConstant extends AbstractReflectedNode
{
    /**
     * @var ServiceLocator
     */
    private $serviceLocator;

    /**
     * @var ConstElement
     */
    private $node;

    public function __construct(
        ServiceLocator $serviceLocator,
        ConstElement $node
    )
    {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
    }

    public function name()
    {
        return $this->node->getName();
    }

    public function type(): Type
    {
        $value = $this->serviceLocator->nodeValueResolver()->resolveNode(new Frame(), $this->node->assignment);
        return $value->type();
    }

    protected function node(): Node
    {
        return $this->node;
    }
}

