<?php

namespace DTL\WorseReflection\Frame\Processor;

use PhpParser\Node;
use DTL\WorseReflection\Frame\Processor;
use DTL\WorseReflection\Frame\Frame;
use DTL\WorseReflection\Frame\FrameBuilder;

class AssignProcessor implements Processor
{
    public function __invoke(Node $node, Frame $frame, FrameBuilder $frameBuilder): Node
    {
        $frame->set($node->var->name, $frameBuilder->__invoke($node->expr, $frame));

        return $node;
    }
}
