<?php

namespace DTL\WorseReflection\Frame\Processor;

use PhpParser\Node;
use DTL\WorseReflection\Frame\Processor;
use DTL\WorseReflection\Frame\Frame;
use DTL\WorseReflection\Frame\NodeDispatcher;

class ClassProcessor implements Processor
{
    public function __invoke(Node $node, Frame $frame, NodeDispatcher $nodeDispatcher): Node
    {
        $frame->set('this', $node);
        return $node;
    }
}
