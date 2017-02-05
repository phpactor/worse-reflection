<?php

namespace DTL\WorseReflection\Frame;

use PhpParser\Node;

class Frame
{
    private $variables = [];

    public function set($name, Node $node)
    {
        $this->variables[$name] = $node;
    }

    public function remove($name)
    {
        unset($this->variables[$name]);
    }

    public function get($name): Node
    {
        if (!isset($this->variables[$name])) {
            throw new \RuntimeException(sprintf(
                'Variable "%s" not found in frame, scoped variables: "%s"',
                $name, implode('", "', array_keys($this->variables))
            ));
        }

        return $this->variables[$name];
    }

    public function all()
    {
        return $this->variables;
    }
}
