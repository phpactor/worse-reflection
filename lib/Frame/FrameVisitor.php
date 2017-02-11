<?php

namespace DTL\WorseReflection\Frame;

use PhpParser\NodeVisitorAbstract;
use PhpParser\NodeTraverser;
use PhpParser\Node;

class FrameVisitor extends NodeVisitorAbstract
{
    private $frame;
    private $nodeDispatcher;

    public function __construct(Frame $frame, NodeDispatcher $nodeDispatcher = null)
    {
        $this->nodeDispatcher = $nodeDispatcher ?: new NodeDispatcher();
        $this->frame = $frame ?: new Frame();
    }

    public function enterNode(Node $node)
    {
        $this->nodeDispatcher->__invoke($node, $this->frame, $traverseChildren);

        if ($traverseChildren === false || $this->isScopeChangingNode($node)) {
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
    }

    private function isScopeChangingNode(Node $node)
    {
        return $node instanceof Node\FunctionLike || $node instanceof Node\Stmt\Class_;
    }
}
