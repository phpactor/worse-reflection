<?php

namespace DTL\WorseReflection\Evaluation;

class Frame
{
    /**
     * @var TypedNode[]
     */
    private $variables;

    public function set($name, NodeValue $nodeValue)
    {
        $this->variables[$name] = $nodeValue;
    }

    public function get($name)
    {
        if (!isset($this->variables[$name])) {
            throw new \RuntimeException(sprintf(
                'Variable "%s" not found in frame, scoped variables: "%s"',
                $name, implode('", "', array_keys($this->variables))
            ));
        }

        return $this->variables[$name];
    }
}
