<?php

namespace Phpactor\WorseReflection\Reflection\Inference;

use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Node\NumericLiteral;
use Microsoft\PhpParser\Node\ReservedWord;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;

final class ValueResolver
{
    public function resolveExpression(Expression $expression)
    {
        if ($expression instanceof StringLiteral) {
            return (string) $expression->getStringContentsText();
        }

        if ($expression instanceof NumericLiteral) {
            // hack to cast to either an int or a float
            return $expression->getText() + 0;
        }

        if ($expression instanceof ReservedWord) {
            if ('null' === $expression->getText()) {
                return null;
            }

            if ('false' === $expression->getText()) {
                return false;
            }

            if ('true' === $expression->getText()) {
                return true;
            }
        }

        if ($expression instanceof ArrayCreationExpression) {
            $array  = [];

            if (null === $expression->arrayElements) {
                return $array;
            }

            foreach ($expression->arrayElements->getElements() as $element) {
                if ($element->elementKey) {
                    $array[(string) $element->elementKey] = $this->resolveExpression($element->elementValue);
                }

                $array[] = $this->resolveExpression($element->elementValue);
            }

            return $array;
        }

        return $expression->getText();
    }
}
