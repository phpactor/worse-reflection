<?php

namespace Phpactor\WorseReflection\Reflection;

use Phpactor\WorseReflection\Visibility;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Phpactor\WorseReflection\ServiceLocator;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\DocblockResolver;
use Microsoft\PhpParser\Node\Parameter;
use Phpactor\WorseReflection\Type;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Node\NumericLiteral;
use Microsoft\PhpParser\Node\ReservedWord;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\DefaultValue;
use Phpactor\WorseReflection\Reflection\Inference\NodeValueResolver;
use Phpactor\WorseReflection\Reflection\Inference\Frame;
use Phpactor\WorseReflection\Reflection\Inference\Value;

class ReflectionParameter extends AbstractReflectedNode
{
    private $serviceLocator;
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
