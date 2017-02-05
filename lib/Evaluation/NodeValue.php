<?php

namespace DTL\WorseReflection\Evaluation;

use DTL\WorseReflection\Type;
use PhpParser\Node;
use DTL\WorseReflection\Evaluation\RawType;

class NodeValue
{
    private $node;
    private $type;

    private function __construct(Node $node, RawType $type)
    {
        $this->node = $node;
        $this->type = $type;
    }

    public static function fromNodeAndType(Node $node, RawType $type)
    {
        $instance = new self($node, $type);

        return $instance;
    }

    public static function fromNode(Node $node)
    {
        $instance = new self($node, RawType::unknown());

        return $instance;
    }

    public static function fromReference(Node $node)
    {
        $instance = new self($node, RawType::unknown());

        return $instance;
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
