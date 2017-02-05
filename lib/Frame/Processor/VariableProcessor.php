<?php

namespace DTL\WorseReflection\Frame\Processor;

use PhpParser\Node;
use DTL\WorseReflection\Frame\Frame;
use DTL\WorseReflection\Frame\FrameBuilder;
use DTL\WorseReflection\Frame\Processor;

class VariableProcessor implements Processor
{
    public function __invoke(Node $node, Frame $frame, FrameBuilder $frameBuilder): Node
    {
        return $frame->get($node->name);
    }
}

