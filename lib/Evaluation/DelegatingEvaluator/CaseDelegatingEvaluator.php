<?php

namespace DTL\WorseReflection\Evaluation\DelegatingEvaluator;

use PhpParser\Node;
use DTL\WorseReflection\Evaluation\DelegatingEvaluator;
use DTL\WorseReflection\Evaluation\Frame;
use DTL\WorseReflection\Evaluation\Evaluator;

class CaseDelegatingEvaluator implements DelegatingEvaluator
{
    public function __invoke(Node $node, Frame $frame)
    {
        if ($node instanceof Node\Scalar) {
            return (new Evaluator\ScalarEvaluator())($node, $frame, $this);
        }

        if ($node instanceof Node\FunctionLike) {
            return (new Evaluator\FunctionLikeEvaluator())($node, $frame, $this);
        }

        if ($node instanceof Node\Expr\Assign) {
            return (new Evaluator\Expr_AssignEvaluator())($node, $frame, $this);
        }

        if ($node instanceof Node\Expr\Variable) {
            return (new Evaluator\Expr_VariableEvaluator())($node, $frame, $this);
        }

        if ($node instanceof Node\Expr\New_) {
            return (new Evaluator\Expr_NewEvaluator())($node, $frame, $this);
        }

        if ($node instanceof Node\Expr\ClassConstFetch) {
            return (new Evaluator\Expr_ClassConstFetchEvaluator())($node, $frame, $this);
        }

        throw new Exception\UnknownNodeException($node);
    }
}
