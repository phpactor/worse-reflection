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
use Microsoft\PhpParser\Node\Statement\ThrowStatement;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Microsoft\PhpParser\Node\Expression\UnaryOpExpression;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Phpactor\WorseReflection\Core\Inference\Assignments;
use Phpactor\WorseReflection\Core\Types;
use Microsoft\PhpParser\Node\ReservedWord;
use Phpactor\WorseReflection\Core\Inference\ExpressionEvaluator;

class InstanceOfWalker implements FrameWalker
{
    /**
     * @var SymbolFactory
     */
    private $symbolFactory;

    /**
     * @var ExpressionEvaluator
     */
    private $evaluator;

    public function __construct(
        SymbolFactory $symbolFactory
    )
    {
        $this->symbolFactory = $symbolFactory;
        $this->evaluator = new ExpressionEvaluator();
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
        if (null === $node->expression) {
            return $frame;
        }

        assert($node instanceof IfStatementNode);

        $expressionsAreTrue = $this->evaluator->evaluate($node->expression);
        $variables = $this->collectVariables($node);
        $variables = $this->mergeTypes($variables);
        $terminates = $this->branchTerminates($node);

        foreach ($variables as $variable) {
            if ($terminates) {

                // reset variables after the if branch
                if ($expressionsAreTrue) {
                    $frame->locals()->add($variable);

                    // restore
                    $restoredVariable = $this->existingOrStripType($node, $frame, $variable);
                    $frame->locals()->add($restoredVariable);
                    continue;
                }

                // create new variables after the if branch
                if (false === $expressionsAreTrue) {
                    $detypedVariable = $variable->withTypes(Types::empty());
                    $frame->locals()->add($detypedVariable);
                    $variable = $variable->withOffset($node->getEndPosition());
                    $frame->locals()->add($variable);
                    continue;
                }

            }

            if ($expressionsAreTrue) {
                $frame->locals()->add($variable);
            }

            if (false === $expressionsAreTrue) {
                $variable = $variable->withTypes(Types::empty());
                $frame->locals()->add($variable);
            }
        }

        return $frame;
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

    private function mergeTypes(array $variables): array
    {
        $vars = [];
        foreach ($variables as $variable) {
            if (isset($vars[$variable->name()])) {
                $originalVariable = $vars[$variable->name()];
                $variable = $originalVariable->withTypes(
                    $originalVariable->symbolContext()
                        ->types()
                        ->merge(
                            $variable->symbolContext()->types()
                        )
                );
            }

            $vars[$variable->name()] = $variable;
        }

        return $vars;
    }

    /**
     * @return WorseVariable[]
     */
    private function collectVariables(Node $node): array
    {
        $variables = [];
        foreach ($node->getDescendantNodes() as $descendantNode) {
            if (!$descendantNode instanceof BinaryExpression) {
                continue;
            }

            $variable = $this->variableFromBinaryExpression($descendantNode);

            if (null === $variable) {
                continue;
            }

            $variables[] = $variable;
        }

        return $variables;
    }

    private function variableFromBinaryExpression(BinaryExpression $node)
    {
        $operator = $node->operator->getText($node->getFileContents());

        if (strtolower($operator) !== 'instanceof') {
            return null;
        }

        $variable = $node->getFirstDescendantNode(Variable::class);
        $rightOperand = $node->rightOperand;

        if (false === $rightOperand instanceof QualifiedName) {
            return null;
        }

        $type = (string) $rightOperand->getNamespacedName();

        $context = $this->createSymbolContext($variable);
        $context = $context->withType(Type::fromString($type));
        $variable = WorseVariable::fromSymbolContext($context);

        return $variable;
    }

    private function existingOrStripType(IfStatementNode $node, Frame $frame, WorseVariable $variable)
    {
        $frameVariable = null;
        foreach ($frame->locals()->lessThan($node->getStart())->byName($variable->name()) as $frameVariable) {
        }

        if (null === $frameVariable) {
            return $variable->withTypes(Types::empty())->withOffset($node->getEndPosition());
        }

        return $frameVariable->withOffset($node->getEndPosition());
    }

}
