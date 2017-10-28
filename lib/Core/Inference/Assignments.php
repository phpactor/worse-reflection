<?php

namespace Phpactor\WorseReflection\Core\Inference;

abstract class Assignments implements \Countable, \IteratorAggregate
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
            return $variable->isNamed($name);
        }));
    }

    public function lessThanOrEqualTo(int $offset): Assignments
    {
        return new static(array_filter($this->variables, function (Variable $variable) use ($offset) {
            return $variable->offset()->toInt() <= $offset;
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

    public function last(): Variable
    {
        $last = end($this->variables);

        if (!$last) {
            throw new \RuntimeException(
                'Cannot get last, variable collection is empty'
            );
        }

        return $last;
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->variables);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->variables);
    }
}
