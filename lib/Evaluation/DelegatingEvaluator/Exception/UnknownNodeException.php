<?php

namespace DTL\WorseReflection\Evaluation\DelegatingEvaluator\Exception;

use PhpParser\Node;

class UnknownNodeException extends \RuntimeException
{
    public function __construct(Node $node)
    {
        $this->node = $node;
        parent::__construct(sprintf(
            'No evaluator could be found for node "%s"', get_class($node)
        ));
    }
}
