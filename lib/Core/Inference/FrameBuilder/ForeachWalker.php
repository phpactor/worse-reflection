<?php

namespace Phpactor\WorseReflection\Core\Inference\FrameBuilder;

use Phpactor\WorseReflection\Core\Inference\FrameWalker;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Microsoft\PhpParser\Node\Statement\ForeachStatement;
use Microsoft\PhpParser\Node\ForeachValue;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\SymbolFactory;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;

class ForeachWalker implements FrameWalker
{
    /**
     * @var SymbolFactory
     */
    private $symbolFactory;

    public function __construct(SymbolFactory $symbolFactory)
    {
        $this->symbolFactory = $symbolFactory;
    }

    public function canWalk(Node $node): bool
    {
        return $node instanceof ForeachStatement;
    }

    public function walk(FrameBuilder $builder, Frame $frame, Node $node): Frame
    {
        assert($node instanceof ForeachStatement);
        $collection = $builder->resolveNode($frame, $node->forEachCollectionName);
        $itemName = $node->foreachValue;

        if (!$itemName instanceof ForeachValue) {
            return $frame;
        }

        if (!$itemName->expression instanceof Variable) {
            return $frame;
        }

        $itemName = $itemName->expression->name->getText($node->getFileContents());

        $collectionType = $collection->types()->best();

        $context = $this->symbolFactory->context(
            $itemName,
            $node->getStart(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
            ]
        );

        if ($collectionType->arrayType()->isDefined()) {
            $context = $context->withType($collectionType->arrayType());
        }

        $frame->locals()->add(WorseVariable::fromSymbolContext($context));

        return $frame;
    }
}
