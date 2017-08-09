<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Visibility;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Core\DocblockResolver;
use Microsoft\PhpParser\Node\Parameter;
use Phpactor\WorseReflection\Core\Type;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Node\NumericLiteral;
use Microsoft\PhpParser\Node\ReservedWord;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\DefaultValue;
use Phpactor\WorseReflection\Core\Reflection\Inference\NodeValueResolver;
use Phpactor\WorseReflection\Core\Reflection\Inference\Frame;
use Phpactor\WorseReflection\Core\Reflection\Inference\Value;
use Phpactor\WorseReflection\Core\Reflection\AbstractReflectedNode;

class ReflectionParameter extends AbstractReflectedNode
{
    /**
     * @var ServiceLocator
     */
    private $serviceLocator;

    /**
     * @var Parameter
     */
    private $parameter;

    public function __construct(ServiceLocator $serviceLocator, Parameter $parameter)
    {
        $this->serviceLocator = $serviceLocator;
        $this->parameter = $parameter;
    }

    public function name(): string
    {
        return $this->parameter->getName();
    }

    public function type(): Type
    {
        // TODO: Generalize this logic (also used in property)
        if ($this->parameter->typeDeclaration instanceof Token) {
            return Type::fromString($this->parameter->typeDeclaration->getText($this->parameter->getFileContents()));
        }

        if ($this->parameter->typeDeclaration) {
            return Type::fromString($this->parameter->typeDeclaration->getResolvedName());
        }

        return Type::undefined();
    }

    public function default(): DefaultValue
    {
        if (null === $this->parameter->default) {
            return DefaultValue::undefined();
        }

        return DefaultValue::fromValue($this->serviceLocator->nodeValueResolver()->resolveNode(new Frame(), $this->parameter)->value());
    }

    protected function node(): Node
    {
        return $this->node;
    }
}

