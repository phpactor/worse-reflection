<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node\ConstElement;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Reflection\ReflectionConstant as CoreReflectionConstant;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Types;
use Microsoft\PhpParser\Node\ClassConstDeclaration;

class ReflectionConstant extends AbstractReflectionClassMember implements CoreReflectionConstant
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

    /**
     * @var ClassConstDeclaration
     */
    private $declaration;

    public function __construct(
        ServiceLocator $serviceLocator,
        AbstractReflectionClass $class,
        ClassConstDeclaration $declaration,
        ConstElement $node
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
        $this->class = $class;
        $this->declaration = $declaration;
    }

    public function name(): string
    {
        return $this->node->getName();
    }

    public function type(): Type
    {
        $value = $this->serviceLocator->symbolContextResolver()->resolveNode(new Frame('test'), $this->node->assignment);
        return $value->type();
    }

    protected function node(): Node
    {
        return $this->declaration;
    }

    protected function serviceLocator(): ServiceLocator
    {
        return $this->serviceLocator;
    }

    public function class(): ReflectionClassLike
    {
        return $this->class;
    }

    public function inferredTypes(): Types
    {
        if (Type::unknown() !== $this->type()) {
            return Types::fromTypes([ $this->type() ]);
        }

        return Types::empty();
    }

    public function isVirtual(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function value()
    {
        return $this->serviceLocator()
                    ->symbolContextResolver()
                    ->resolveNode(
                        new Frame('_'),
                        $this->node->assignment
                    )->value();
    }
}
