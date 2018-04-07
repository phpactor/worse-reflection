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
        if (null === $node->expression) {
            return $frame;
        }

        $operator = $node->expression->operator->getText($node->getFileContents());

        if (strtolower($operator) !== 'instanceof') {
            return $frame;
        }

        /** @var Variable $leftOperand */
        $leftOperand = $node->expression->leftOperand;

        if (false === $leftOperand instanceof Variable) {
            return $frame;
        }

        $rightOperand = $node->expression->rightOperand;

        if (false === $rightOperand instanceof QualifiedName) {
            return $frame;
        }

        $type = (string) $rightOperand->getNamespacedName();

        $context = $this->createSymbolContext($leftOperand);
        $context = $context->withType(Type::fromString($type));

        $frame->locals()->add(WorseVariable::fromSymbolContext($context));

        return $frame;

    }

    private function terminateScope(Frame $frame, IfStatementNode $node, Variable $variable): Frame
    {
        foreach($node->statements as $list) {
            foreach ($list as $statement) {
                if ($statement instanceof ReturnStatement) {
                    $context = $this->createSymbolContext($variable);
                    return $frame;
                }
            }
        }

        return $frame;
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
}
