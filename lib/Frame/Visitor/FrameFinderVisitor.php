<?php

namespace DTL\WorseReflection\Frame\Visitor;

use DTL\WorseReflection\Frame\NodeDispatcher;
use PhpParser\NodeVisitorAbstract;
use DTL\WorseReflection\Frame\Frame;
use PhpParser\NodeTraverser;
use PhpParser\Node;
use DTL\WorseReflection\Frame\FrameStack;

class FrameFinderVisitor extends NodeVisitorAbstract
{
    private $frameStack;
    private $frame;
    private $nodeDispatcher;
    private $offset;
    private $done = false;

    public function __construct(
        int $offset,
        NodeDispatcher $nodeDispatcher = null,
        FrameStack $frameStack = null
    )
    {
        $this->nodeDispatcher = $nodeDispatcher ?: new NodeDispatcher();
        $this->frameStack = $frameStack ?: new FrameStack();
        $this->offset = $offset;
        $this->frame = $this->frameStack->spawn();
    }

    public function enterNode(Node $node)
    {
        list($startPos, $endPos) = $this->getStartEndPos($node);

        if ($startPos > $this->offset) {
            $this->done = true;
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        if ($this->isScopeChangingNode($node)) {
            if ($node instanceof Node\Stmt\ClassMethod) {
                $this->frameStack->spawnWith(['this']);
            } else {
                $this->frameStack->spawn();
            }
        }

        $this->nodeDispatcher->__invoke($node, $this->frameStack->top(), $traverseChildren);

        $this->frame = $this->frameStack->top();

        if ($traverseChildren === false) {
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
    }

    public function leaveNode(Node $node)
    {
        if (false === $this->done && $this->isScopeChangingNode($node)) {
            $this->frameStack->pop();
        }
    }

    public function getFrame()
    {
        return $this->frame;
    }

    private function isScopeChangingNode(Node $node)
    {
        return $node instanceof Node\FunctionLike || $node instanceof Node\Stmt\Class_;
    }

    private function getStartEndPos(Node $node)
    {
        $startPos = $node->getAttribute('startFilePos');
        $endPos = $node->getAttribute('endFilePos');

        if (null === $startPos) {
            throw new \RuntimeException(
                'Node does not have the startFilePos attribute'
            );
        }

        if (null === $endPos) {
            throw new \RuntimeException(
                'Node does not have the endFilePos attribute'
            );
        }

        return [ $startPos, $endPos ];
    }
}
