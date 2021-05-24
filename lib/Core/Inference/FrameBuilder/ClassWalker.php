<?php

namespace Phpactor\WorseReflection\Core\Inference\FrameBuilder;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder;
use Phpactor\WorseReflection\Core\Inference\FrameWalker;

class ClassWalker implements FrameWalker
{
    public function canWalk(Node $node): bool
    {
        return $node instanceof QualifiedName;
    }

    public function walk(FrameBuilder $builder, Frame $frame, Node $node): Frame
    {
        $node = $builder->resolveNode($frame, $node);

        return $frame;
    }
}
