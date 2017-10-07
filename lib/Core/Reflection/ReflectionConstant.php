<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Microsoft\PhpParser\Node\ConstElement;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Inference\Frame;

class ReflectionConstant extends AbstractReflectionClassMember
{
    /**
     * @var ServiceLocator
     */
    private $serviceLocator;

    /**
     * @var ConstElement
     */
    private $node;

    /**
     * @var AbstractReflectionClass
     */
    private $class;

    public function __construct(
        ServiceLocator $serviceLocator,
        AbstractReflectionClass $class,
        ConstElement $node
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
        $this->class = $class;
    }

    public function name()
    {
        return $this->node->getName();
    }

    public function type(): Type
    {
        $value = $this->serviceLocator->symbolInformationResolver()->resolveNode(new Frame(), $this->node->assignment);
        return $value->type();
    }

    protected function node(): Node
    {
        return $this->node;
    }

    protected function serviceLocator(): ServiceLocator
    {
        return $this->serviceLocator;
    }

    public function class(): AbstractReflectionClass
    {
        return $this->class;
    }
}

