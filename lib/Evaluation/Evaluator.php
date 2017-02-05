<?php

namespace DTL\WorseReflection\Evaluation;

use PhpParser\Node;

interface Evaluator
{
    public function __invoke(Node $node, Frame $frame, DelegatingEvaluator $evaluator): NodeValue;
}
