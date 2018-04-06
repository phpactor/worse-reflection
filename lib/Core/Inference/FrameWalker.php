<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder\Frame;

interface FrameWalker
{
    public function canWalk(Node $node);

    public function walk(Frame $frame, Node $node);
}
