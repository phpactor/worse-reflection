<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\NodeText;

final class NodeText
{
    private $nodeText;

    private function __construct($nodeText)
    {
        $this->nodeText = $nodeText;
    }

    public static function fromString(string $nodeText): NodeText
    {
         return new self($nodeText);
    }

    public function __toString()
    {
        return $this->nodeText;
    }
}
