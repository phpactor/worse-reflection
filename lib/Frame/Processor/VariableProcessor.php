<?php

namespace DTL\WorseReflection\Frame\Processor;

use PhpParser\Node;
use DTL\WorseReflection\Frame\Frame;
use DTL\WorseReflection\Frame\NodeDispatcher;
use DTL\WorseReflection\Frame\Processor;

class VariableProcessor implements Processor
{
    public function __invoke(Node $node, Frame $frame, NodeDispatcher $nodeDispatcher): Node
    {
        return $frame->get($node->name);
    }
}

