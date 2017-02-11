<?php

namespace DTL\WorseReflection\Frame;

use PhpParser\Node;
use DTL\WorseReflection\Frame\Frame;

class NodeDispatcher
{
    public function __invoke(Node $node, Frame $frame, &$traverseChildren = true)
    {
        // introduces new variables into scope from parameters
        if ($node instanceof Node\FunctionLike) {
            return (new Processor\FunctionLikeProcessor())($node, $frame, $this);
        }

        if ($node instanceof Node\Stmt\Class_) {
            return (new Processor\ClassProcessor())($node, $frame, $this);
        }

        // introduces new variables from direct assignment
        if ($node instanceof Node\Expr\Assign) {
            return (new Processor\AssignProcessor())($node, $frame, $this);
        }

        // updates existing variables
        if ($node instanceof Node\Expr\Variable) {
            return (new Processor\VariableProcessor())($node, $frame, $this);
        }

        // remove nodes from scope
        if ($node instanceof Node\Stmt\Unset_) {
            // we do not want the variables in the unset node to be
            // traversed after they have been unset, as the variable processor
            // will be invoked and it will ask for the variable from the Frame,
            // and an exception would be thrown.
            $traverseChildren = false;
            return (new Processor\UnsetProcessor())($node, $frame, $this);
        }

        return $node;
    }
}
