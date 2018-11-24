<?php

namespace Phpactor\WorseReflection\Core\Inference\FrameBuilder;

use Phpactor\WorseReflection\Core\Inference\FrameWalker;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\WorseReflection\Core\Inference\SymbolFactory;
use Microsoft\PhpParser\Node\CatchClause;
use Phpactor\WorseReflection\Core\Inference\Symbol;

class CatchWalker extends AbstractWalker
{
    public function canWalk(Node $node): bool
    {
        return $node instanceof CatchClause;
    }

    public function walk(FrameBuilder $builder, Frame $frame, Node $node): Frame
    {
        assert($node instanceof CatchClause);
        if (!$node->qualifiedName) {
            return $frame;
        }

        $typeContext = $builder->resolveNode($frame, $node->qualifiedName);
        $context = $this->symbolFactory()->context(
            $node->variableName->getText($node->getFileContents()),
            $node->variableName->getStartPosition(),
            $node->variableName->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => $typeContext->type(),
            ]
        );

        $frame->locals()->add(Variable::fromSymbolContext($context));

        return $frame;
    }
}
