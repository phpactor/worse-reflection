<?php

namespace DTL\WorseReflection\Evaluation\Evaluator;

use DTL\WorseReflection\Evaluation\Evaluator;
use PhpParser\Node;
use DTL\WorseReflection\Evaluation\Frame;
use DTL\WorseReflection\Evaluation\DelegatingEvaluator;
use DTL\WorseReflection\Evaluation\NodeValue;

class Expr_VariableEvaluator implements Evaluator
{
    public function __invoke(Node $node, Frame $frame, DelegatingEvaluator $evaluator): NodeValue
    {
        return $frame->get($node->name);
    }
}

