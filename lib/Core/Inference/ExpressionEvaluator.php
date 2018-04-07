<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\DefaultValue;
use Microsoft\PhpParser\Node\ReservedWord;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Microsoft\PhpParser\Node\NumericLiteral;
use Microsoft\PhpParser\Node\Expression\PostfixUpdateExpression;
use RuntimeException;
use Microsoft\PhpParser\Node\Expression\PrefixUpdateExpression;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Node\Expression\UnaryOpExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Expression\ParenthesizedExpression;
use PhpParser\Node\Expr\Ternary;
use Microsoft\PhpParser\Node\Expression\TernaryExpression;

class ExpressionEvaluator
{
    public function evaluate(Node $node)
    {
        if ($node instanceof ReservedWord) {
            return $this->walkReservedWord($node);
        }

        if ($node instanceof BinaryExpression) {
            return $this->walkBinaryExpression($node);
        }

        if ($node instanceof NumericLiteral) {
            return $this->walkNumericLiteral($node);
        }

        if ($node instanceof StringLiteral) {
            return $this->walkStringLiteral($node);
        }

        if ($node instanceof PrefixUpdateExpression) {
            return $this->walkPrefixUpdateExpression($node);
        }

        if ($node instanceof PostfixUpdateExpression) {
            return $this->walkPostfixUpdateExpression($node);
        }
 
        if ($node instanceof UnaryOpExpression) {
            return $this->walkUnaryOpExpression($node);
        }

        if ($node instanceof Variable) {
            return $this->walkVariable($node);
        }

        if ($node instanceof ParenthesizedExpression) {
            return $this->walkParenthesizedExpression($node);
        }

        if ($node instanceof TernaryExpression) {
            return $this->walkTernary($node);
        }
    }

    private function walkReservedWord(ReservedWord $node)
    {
        switch (strtolower($node->getText())) {
            case 'true':
                return true;
            case 'false':
                return false;
        }

        return null;
    }

    private function walkBinaryExpression(BinaryExpression $node)
    {
        $leftValue = $this->evaluate($node->leftOperand);
        $rightValue = $this->evaluate($node->rightOperand);
        $operator = strtolower($node->operator->getText($node->getFileContents()));

        switch ($operator) {
            case '===':
                return $leftValue === $rightValue;
            case '==':
                return $leftValue == $rightValue;
            case '>':
                return $leftValue > $rightValue;
            case '>=':
                return $leftValue > $rightValue;
            case 'or':
                return $leftValue or $rightValue;
            case '||':
                return $leftValue or $rightValue;
            case 'and':
                return $leftValue and $rightValue;
            case '&&':
                return $leftValue && $rightValue;
            case '+':
                return $leftValue + $rightValue;
            case '-':
                return $leftValue - $rightValue;
            case '%':
                return $leftValue % $rightValue;
            case '/':
                return $leftValue / $rightValue;
            case '.':
                return $leftValue . $rightValue;
            case '.=':
                return $leftValue .= $rightValue;
            case 'instanceof':
                return true;
            case '>':
                return $leftValue > $rightValue;
            case '<':
                return $leftValue < $rightValue;
            case '<=':
                return $leftValue <= $rightValue;
            case '!==':
                return $leftValue !== $rightValue;
            case '!==':
                return $leftValue !== $rightValue;
            case '&':
                return $leftValue & $rightValue;
            case '|':
                return $leftValue & $rightValue;
            case '^':
                return $leftValue ^ $rightValue;
            case '<<':
                return $leftValue << $rightValue;
            case '>>':
                return $leftValue >> $rightValue;
            case 'xor':
                return $leftValue xor $rightValue;
        }
    }

    private function walkNumericLiteral(NumericLiteral $node)
    {
        $number = floatval($node->getText());

        if (false === strpos($number, '.')) {
            return (int) $number;
        }

        return (float) $number;
    }

    private function walkPrefixUpdateExpression(PrefixUpdateExpression $node)
    {
        $value = $this->evaluate($node->operand);
        $suffix = $node->incrementOrDecrementOperator->getText($node->getFileContents());

        switch ($suffix) {
            case '++':
                return ++$value;
            case '--':
                return --$value;
        }
    }

    private function walkPostfixUpdateExpression(PostfixUpdateExpression $node)
    {
        $value = $this->evaluate($node->operand);
        $suffix = $node->incrementOrDecrementOperator->getText($node->getFileContents());

        return $value;
    }

    private function walkStringLiteral(StringLiteral $node)
    {
        return (string) $node->getStringContentsText();
    }

    private function walkUnaryOpExpression(UnaryOpExpression $node)
    {
        $operator = $node->operator->getText($node->getFileContents());
        $value = $this->evaluate($node->operand);

        switch ($operator) {
            case '~':
                return ~$value;
            case '!':
                return !$value;
        }
    }

    private function walkVariable(Variable $node)
    {
        return true;
    }

    private function walkParenthesizedExpression(ParenthesizedExpression $node)
    {
        return $this->evaluate($node->expression);
    }

    private function walkTernary(TernaryExpression $node)
    {
        $condition = $this->evaluate($node->condition);

        if (!$node->elseExpression) {
            return null;
        }

        $else = $this->evaluate($node->elseExpression);
        if ($node->ifExpression) {
            $if = $this->evaluate($node->ifExpression);
            return $condition ? $if : $else;
        }

        return $condition ?: $else;
    }
}
