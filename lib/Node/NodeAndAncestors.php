<?php

namespace DTL\WorseReflection\Node;

class NodeAndAncestors
{
    private $nodes;
    private $index;

    public function __construct(array $nodes)
    {
        $this->nodes = $nodes;
        $this->size = count($nodes);
        $this->index = $this->size;
    }

    public function top()
    {
        return end($this->nodes);
    }

    public function seekBack(\Closure $predicate)
    {
        while ($this->index > 0 && $this->index <= $this->size) {
            $this->index--;
            $node = $this->nodes[$this->index];

            if (false === $predicate($node)) {
                continue;
            }

            return $node;
        }

        return null;
    }
}
