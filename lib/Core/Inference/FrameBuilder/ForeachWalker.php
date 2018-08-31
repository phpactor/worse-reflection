<?php

namespace Phpactor\WorseReflection\Core\Inference\FrameBuilder;

use Microsoft\PhpParser\Node\ForeachKey;
use Phpactor\WorseReflection\Core\Inference\FrameWalker;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Microsoft\PhpParser\Node\Statement\ForeachStatement;
use Microsoft\PhpParser\Node\ForeachValue;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\SymbolContext;
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
        $this->processKey($node, $frame, $collection);
        $this->processValue($node, $frame, $collection);

        return $frame;
    }

    private function processValue(ForeachStatement $node, Frame $frame, SymbolContext $collection)
    {
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
    }

    private function processKey(ForeachStatement $node, Frame $frame, SymbolContext $collection)
    {
        $itemName = $node->foreachKey;
        
        if (!$itemName instanceof ForeachKey) {
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
            $node->getEndPosition()
        );
        
        $frame->locals()->add(WorseVariable::fromSymbolContext($context));
    }
}
