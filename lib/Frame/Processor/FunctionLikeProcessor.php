<?php

namespace DTL\WorseReflection\Frame\Processor;

use PhpParser\Node;
use DTL\WorseReflection\Frame\Processor;
use DTL\WorseReflection\Frame\Frame;
use DTL\WorseReflection\Frame\FrameBuilder;
use DTL\WorseReflection\Frame\Processor\FunctionLikeProcessor;

class FunctionLikeProcessor implements Processor
{
    public function __invoke(Node $node, Frame $frame, FrameBuilder $frameBuilder): Node
    {
        foreach ($node->params as $param) {
            $frame->set($param->name, $param);
        }

        return $node;
    }
}

