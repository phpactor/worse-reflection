<?php

namespace DTL\WorseReflection\Evaluation;

use PhpParser\Node;
use DTL\WorseReflection\Evaluation\Frame;

interface DelegatingEvaluator
{
    public function __invoke(Node $node, Frame $frame);
}
