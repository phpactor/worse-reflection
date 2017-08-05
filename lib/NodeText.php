<?php

namespace Phpactor\WorseReflection;

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
