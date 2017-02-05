<?php

namespace DTL\WorseReflection\Evaluation\Evaluator;

use DTL\WorseReflection\Evaluation\Evaluator;
use PhpParser\Node;
use DTL\WorseReflection\Evaluation\Frame;
use DTL\WorseReflection\Evaluation\DelegatingEvaluator;
use DTL\WorseReflection\Evaluation\NodeValue;
use DTL\WorseReflection\Evaluation\RawType;
use DTL\WorseReflection\Evaluation\Reference\ClassConstReference;

class Expr_ClassConstFetchEvaluator implements Evaluator
{
    public function __invoke(Node $node, Frame $frame, DelegatingEvaluator $evaluator): NodeValue
    {
        return NodeValue::fromReference($node, new ClassConstReference((string) $node->class, $node->name));
    }
}

