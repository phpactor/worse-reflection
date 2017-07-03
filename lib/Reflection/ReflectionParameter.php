<?php

namespace DTL\WorseReflection\Reflection;

use DTL\WorseReflection\Visibility;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use DTL\WorseReflection\Reflector;
use Microsoft\PhpParser\TokenKind;
use DTL\WorseReflection\DocblockResolver;
use Microsoft\PhpParser\Node\Parameter;
use DTL\WorseReflection\Type;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Node\NumericLiteral;
use Microsoft\PhpParser\Node\ReservedWord;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Expression;

class ReflectionParameter
{
    private $reflector;
    private $parameter;

    public function __construct(Reflector $reflector, Parameter $parameter)
    {
        $this->reflector = $reflector;
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

        return Type::fromString($this->parameter->typeDeclaration->getResolvedName());
    }

    public function hasDefault()
    {
        return null !== $this->parameter->default;
    }

    public function hasType()
    {
        return null !== $this->parameter->typeDeclaration;
    }

    public function default()
    {
        $default = $this->parameter->default;

        return $this->resolveValue($default);
    }

    private function resolveValue(Expression $expression)
    {
        if ($expression instanceof StringLiteral) {
            return (string) $expression->getStringContentsText();
        }

        if ($expression instanceof NumericLiteral) {
            return $expression->getText();
        }

        if ($expression instanceof ReservedWord) {
            if ('null' === $expression->getText()) {
                return null;
            }
        }

        if ($expression instanceof ArrayCreationExpression) {
            $array  = [];

            if (null === $expression->arrayElements) {
                return $array;
            }

            foreach ($expression->arrayElements->getElements() as $element) {
                if ($element->elementKey) {
                    $array[(string) $element->elementKey] = $this->resolveValue($element->elementValue);
                }

                $array[] = $this->resolveValue($element->elementValue);
            }

            return $array;
        }

        return $expression->getText();
    }
}
