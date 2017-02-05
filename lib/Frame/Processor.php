<?php

namespace DTL\WorseReflection\Frame;

use PhpParser\Node;
use DTL\WorseReflection\Frame\Frame;
use DTL\WorseReflection\Frame\FrameBuilder;

interface Processor
{
    public function __invoke(Node $node, Frame $frame, FrameBuilder $frameBuilder): Node;
}
