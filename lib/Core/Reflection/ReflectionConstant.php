<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Microsoft\PhpParser\Node\ConstElement;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Reflection\Inference\Frame;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Core\ClassName;

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
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
    }

    public function class(): AbstractReflectionClass
    {
        $class = $this->node->getFirstAncestor(ClassDeclaration::class, InterfaceDeclaration::class, TraitDeclaration::class)->getNamespacedName();

        if (null === $class) {
            throw new \InvalidArgumentException(sprintf(
                'Could not locate class-like ancestor node for method "%s"',
                $this->name()
            ));
        }

        return $this->serviceLocator->reflector()->reflectClass(ClassName::fromString($class));
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
}
