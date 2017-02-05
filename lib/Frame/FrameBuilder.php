<?php

namespace DTL\WorseReflection\Frame;

use PhpParser\Node;
use DTL\WorseReflection\Frame\Frame;

class FrameBuilder
{
    public function __invoke(Node $node, Frame $frame)
    {
        // introduces new variables into scope from parameters
        if ($node instanceof Node\FunctionLike) {
            return (new Processor\FunctionLikeProcessor())($node, $frame, $this);
        }

        // introduces new variables from direct assignment
        if ($node instanceof Node\Expr\Assign) {
            return (new Processor\AssignProcessor())($node, $frame, $this);
        }

        // updates existing variables
        if ($node instanceof Node\Expr\Variable) {
            return (new Processor\VariableProcessor())($node, $frame, $this);
        }

        return $node;
    }
}
