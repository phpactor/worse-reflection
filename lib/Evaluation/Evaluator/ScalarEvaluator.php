<?php

namespace DTL\WorseReflection\Evaluation\Evaluator;

use DTL\WorseReflection\Evaluation\Evaluator;
use PhpParser\Node;
use DTL\WorseReflection\Evaluation\Frame;
use DTL\WorseReflection\Evaluation\DelegatingEvaluator;
use DTL\WorseReflection\Evaluation\NodeValue;
use PhpParser\Node\Scalar;
use DTL\WorseReflection\Evaluation\RawType;

class ScalarEvaluator implements Evaluator
{
    public function __invoke(Node $node, Frame $frame, DelegatingEvaluator $evaluator): NodeValue
    {
        if (
            $node instanceof Scalar\String_ ||
            $node instanceof Scalar\EncapsedString
        ) {
            return NodeValue::fromNodeAndType($node, RawType::fromString('string'));
        }

        if ($node instanceof Scalar\DNumber_) {
            return NodeValue::fromNodeAndType($node, RawType::fromString('float'));
        }

        if ($node instanceof Scalar\LNumber_) {
            return NodeValue::fromNodeAndType($node, RawType::fromString('int'));
        }

        return NodeValue::fromNode($node);
    }
}
