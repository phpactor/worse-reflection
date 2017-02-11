<?php

namespace DTL\WorseReflection\Reflection\Collection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContext;
use PhpParser\Node;
use DTL\WorseReflection\Frame\Frame;
use PhpParser\NodeTraverser;
use DTL\WorseReflection\Frame\FrameVisitor;
use PhpParser\Node\FunctionLike;
use DTL\WorseReflection\Reflection\ReflectionVariable;

/**
 * Note this collection only represents the state of the frame
 * at the *end* of the function-like node.
 *
 * In the future it may make more sense to return an array of all variables
 * that were assigned in the scope including those which were assigned to the
 * same name. Which may make sense, f.e. when trying to assertain the frame
 * state at a given offset, or just to have a definitive list of assigned
 * variables.
 *
 * It is FOR THIS reason that we return a numerically indexed
 * array of variables, rather than a map.
 */
class ReflectionVariableCollection implements \IteratorAggregate
{
    /**
     * @var Frame
     */
    private $frame;

    public function __construct(Reflector $reflector, SourceContext $sourceContext, FunctionLike $methodNode)
    {
        $frame = new Frame();
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new FrameVisitor($frame));
        $traverser->traverse($methodNode->stmts);
        $this->frame = $frame;
    }

    public function getIterator(): \Iterator
    {
        $variables = [];
        foreach ($this->frame->all() as $variableName => $variableNode) {
            $variables[] = new ReflectionVariable($variableName, $variableNode);
        }

        return new \ArrayIterator($variables);
    }
}
