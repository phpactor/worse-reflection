<?php

namespace DTL\WorseReflection\Frame\Processor;

use PhpParser\Node;
use DTL\WorseReflection\Frame\Processor;
use DTL\WorseReflection\Frame\Frame;
use DTL\WorseReflection\Frame\NodeDispatcher;

class FunctionLikeProcessor implements Processor
{
    public function __invoke(Node $node, Frame $frame, NodeDispatcher $nodeDispatcher): Node
    {
        foreach ($node->params as $param) {
            $frame->set($param->name, $param);
        }

        return $node;
    }
}

