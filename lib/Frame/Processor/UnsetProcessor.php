<?php

namespace DTL\WorseReflection\Frame\Processor;

use PhpParser\Node;
use DTL\WorseReflection\Frame\Processor;
use DTL\WorseReflection\Frame\Frame;
use DTL\WorseReflection\Frame\NodeDispatcher;

class UnsetProcessor implements Processor
{
    public function __invoke(Node $node, Frame $frame, NodeDispatcher $nodeDispatcher): Node
    {
        foreach ($node->vars as $unsetNode) {
            $frame->remove($unsetNode->name);
        }

        return $node;
    }
}
