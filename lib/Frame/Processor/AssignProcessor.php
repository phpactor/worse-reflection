<?php

namespace DTL\WorseReflection\Frame\Processor;

use PhpParser\Node;
use DTL\WorseReflection\Frame\Processor;
use DTL\WorseReflection\Frame\Frame;
use DTL\WorseReflection\Frame\NodeDispatcher;

class AssignProcessor implements Processor
{
    public function __invoke(Node $node, Frame $frame, NodeDispatcher $nodeDispatcher): Node
    {
        $assignedNode = $nodeDispatcher->__invoke($node->expr, $frame);

        if ($node->var instanceof Node\Expr\List_) {
            foreach ($node->var->items as $index => $item) {
                $assignedItem = $assignedNode->items[$index];
                $frame->set(
                    $item->value->name,
                    $nodeDispatcher->__invoke($assignedItem->value, $frame)
                );
            }

            return $assignedNode;
        }

        $frame->set($node->var->name, $assignedNode);

        return $assignedNode;
    }
}
