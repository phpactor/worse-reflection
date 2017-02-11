<?php

namespace DTL\WorseReflection\Frame;

use PhpParser\Node;

interface Processor
{
    public function __invoke(Node $node, Frame $frame, NodeDispatcher $nodeDispatcher): Node;
}
