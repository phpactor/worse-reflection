<?php

namespace Phpactor\WorseReflection\Core\Inference\FrameBuilder;

use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\ExpressionEvaluator;
use Phpactor\WorseReflection\Core\Inference\SymbolFactory;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Type;

class AbstractInstanceOfWalker
{
    /**
     * @var SymbolFactory
     */
    private $symbolFactory;

    /**
     * @var ExpressionEvaluator
     */
    protected $evaluator;

    public function __construct(
        SymbolFactory $symbolFactory
    ) {
        $this->symbolFactory = $symbolFactory;
        $this->evaluator = new ExpressionEvaluator();
    }
    /**
     * @return WorseVariable[]
     */
    protected function collectVariables(Node $node): array
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

    protected function variableFromBinaryExpression(BinaryExpression $node)
    {
        $operator = $node->operator->getText($node->getFileContents());

        if (strtolower($operator) !== 'instanceof') {
            return null;
        }

        $variable = $node->getFirstDescendantNode(Variable::class);

        if (null === $variable) {
            return null;
        }

        $rightOperand = $node->rightOperand;

        if (false === $rightOperand instanceof QualifiedName) {
            return null;
        }

        $type = (string) $rightOperand->getResolvedName();

        $context = $this->createSymbolContext($variable);
        $context = $context->withType(Type::fromString($type));
        $variable = WorseVariable::fromSymbolContext($context);

        return $variable;
    }

    protected function createSymbolContext(Variable $leftOperand)
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
}
