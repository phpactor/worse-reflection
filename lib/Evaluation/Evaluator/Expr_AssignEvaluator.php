<?php

namespace DTL\WorseReflection\Evaluation\Evaluator;

use DTL\WorseReflection\Evaluation\Evaluator;
use PhpParser\Node;
use DTL\WorseReflection\Evaluation\Frame;
use DTL\WorseReflection\Evaluation\DelegatingEvaluator;
use DTL\WorseReflection\Evaluation\NodeValue;

class Expr_AssignEvaluator implements Evaluator
{
    public function __invoke(Node $node, Frame $frame, DelegatingEvaluator $evaluator): NodeValue
    {
        $nodeValue = $evaluator->__invoke($node->expr, $frame, $evaluator);

        $frame->set($node->var->name, $nodeValue);

        return $nodeValue;
    }
}
