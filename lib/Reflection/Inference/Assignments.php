<?php

namespace Phpactor\WorseReflection\Reflection\Inference;

use Phpactor\WorseReflection\Reflection\Inference\Value;

abstract class Assignments implements \Countable
{
    /**
     * @var array
     */
    private $variables = [];

    protected function __construct(array $variables)
    {
        foreach ($variables as $variable) {
            $this->add($variable);
        }
    }

    public function add(Variable $variable)
    {
        $this->variables[] = $variable;
    }

    public function byName(string $name): Assignments
    {
        return new static(array_filter($this->variables, function (Variable $variable) use ($name) {
            return $variable->name() === $name;
        }));
    }

    public function first(): Variable
    {
        $first = reset($this->variables);

        if (!$first) {
            throw new \RuntimeException(
                'Variable collection is empty'
            );
        }

        return $first;
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->variables);
    }
}

