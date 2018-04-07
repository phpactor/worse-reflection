<?php

namespace Phpactor\WorseReflection\Core\Inference\FrameBuilder;

use Phpactor\WorseReflection\Core\Inference\FrameWalker;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder;
use Microsoft\PhpParser\Node\Statement\IfStatementNode;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\WorseReflection\Core\Inference\SymbolFactory;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Type;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Microsoft\PhpParser\Node\Statement\ThrowStatement;
use Microsoft\PhpParser\Node\Expression\UnaryOpExpression;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Phpactor\WorseReflection\Core\Inference\Assignments;
use Phpactor\WorseReflection\Core\Types;

class InstanceOfWalker implements FrameWalker
{
    /**
     * @var SymbolFactory
     */
    private $symbolFactory;

    public function __construct(
        SymbolFactory $symbolFactory
    )
    {
        $this->symbolFactory = $symbolFactory;
    }
    public function canWalk(Node $node): bool
    {
        return $node instanceof IfStatementNode;
    }

    /**
     * @param IfStatementNode $node
     */
    public function walk(FrameBuilder $builder, Frame $frame, Node $node): Frame
    {
        $declaredVariables = $this->walkNode($builder, $frame, $node, false);
        $terminates = $this->branchTerminates($node);
        $declaredVariables = $this->mergeTypes($declaredVariables);

        foreach ($declaredVariables as $declaredVariable) {
            list($declaredVariable, $negated) = $declaredVariable;

            if ($terminates) {

                // reset variables after the if branch
                if (false === $negated) {
                    $frame->locals()->add($declaredVariable);
                    $detypedVariable = $declaredVariable->withTypes(Types::empty())->withOffset($node->getEndPosition());
                    $frame->locals()->add($detypedVariable);
                }

                // create new variables after the if branch
                if (true === $negated) {
                    $variable = $declaredVariable->withTypes(Types::empty());
                    $frame->locals()->add($variable);
                    $variable = $declaredVariable->withOffset($node->getEndPosition());
                    $frame->locals()->add($variable);
                }

                return $frame;
            }

            if (false === $negated) {
                $frame->locals()->add($declaredVariable);
            }

            if (true === $negated) {
                $variable = $declaredVariable->withTypes(Types::empty());
                $frame->locals()->add($variable);
            }
        }

        return $frame;
    }

    /**
     * @return WorseVariable[]
     */
    public function walkNode(FrameBuilder $builder, Frame $frame, Node $node, bool $negated, Variable $targetVariable = null, Type $type = null): array
    {
        $variables = [];
        $negatedVariables = [];

        if ($node instanceof IfStatementNode) {
            return $this->walkNode($builder, $frame, $node->expression, $negated);
        }

        if ($node instanceof UnaryOpExpression) {
            if ($node->operator->getText($node->getFileContents()) == '!') {
                $negated = !$negated;
            }
        }

        if ($node instanceof Variable) {
            $targetVariable = $node;
        }

        if ($node instanceof BinaryExpression) {
            $type = $this->typeFromBinaryExpression($node);
        }

        if ($type && $targetVariable) {
            $context = $this->createSymbolContext($targetVariable);
            $context = $context->withTypes(Types::fromTypes([ $type ]));
            $variable = WorseVariable::fromSymbolContext($context);

            $variables[] = [$variable, $negated];
        }

        foreach ($node->getChildNodes() as $childNode) {
            foreach ($this->walkNode($builder, $frame, $childNode, $negated, $targetVariable, $type) as $variable) {
                $variables[] = $variable;
            }
        }

        return $variables;
    }

    private function branchTerminates(IfStatementNode $node): bool
    {
        foreach($node->statements as $list) {
            foreach ($list as $statement) {
                if ($statement instanceof ReturnStatement) {
                    return true;
                }

                if ($statement instanceof ThrowStatement) {
                    return true;
                }
            }
        }

        return false;
    }

    private function createSymbolContext(Variable $leftOperand)
    {
        $context = $this->symbolFactory->context(
            $leftOperand->getName(),
            $leftOperand->getStart(),
            $leftOperand->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
            ]
        );

        return $context;
    }

    private function typeFromBinaryExpression(Expression $expression): Type
    {
        $operator = $expression->operator->getText($expression->getFileContents());

        if (strtolower($operator) !== 'instanceof') {
            return Type::unknown();
        }

        $rightOperand = $expression->rightOperand;

        if (false === $rightOperand instanceof QualifiedName) {
            return Type::unknown();
        }

        $type = (string) $rightOperand->getNamespacedName();

        return Type::fromString($type);

        $context = $this->createSymbolContext($leftOperand);
        $context = $context->withType(Type::fromString($type));
        $variable = WorseVariable::fromSymbolContext($context);

        return $variable;
    }

    private function mergeTypes(array $variableTuples): array
    {
        $vars = [];
        foreach ($variableTuples as $variableTuple) {
            list($variable, $negated) = $variableTuple;

            if (isset($vars[$variable->name()])) {
                list($originalVariable) = $vars[$variable->name()];
                $variable = $originalVariable->withTypes(
                    $originalVariable->symbolContext()->types()->merge($variable->symbolContext()->types())
                );
            }

            $vars[$variable->name()] = [ $variable, $negated ];
        }

        return $vars;
    }
}
