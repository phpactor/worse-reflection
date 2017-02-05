<?php

namespace DTL\WorseReflection\Evaluation\Evaluator;

use DTL\WorseReflection\Evaluation\Evaluator;
use PhpParser\Node;
use DTL\WorseReflection\Evaluation\Frame;
use DTL\WorseReflection\Evaluation\DelegatingEvaluator;
use DTL\WorseReflection\Evaluation\NodeValue;
use DTL\WorseReflection\Evaluation\RawType;

class FunctionLikeEvaluator implements Evaluator
{
    public function __invoke(Node $node, Frame $frame, DelegatingEvaluator $evaluator): NodeValue
    {
        foreach ($node->params as $param) {
            $frame->set($param->name, NodeValue::fromNodeAndType($param, RawType::fromString($param->type)));
        }

        return NodeValue::fromNode($node);
    }
}

