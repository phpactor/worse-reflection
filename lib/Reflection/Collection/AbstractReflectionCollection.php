<?php

namespace DTL\WorseReflection\Reflection\Collection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContext;
use PhpParser\Node;

abstract class AbstractReflectionCollection implements \IteratorAggregate
{
    private $reflections;
    private $context;

    public function __construct(
        string $context,
        array $reflections
    )
    {
        $this->reflections = $reflections;
        $this->context = $context;
    }

    public function has($name): bool
    {
        return isset($this->reflections[$name]);
    }

    public function get(string $name)
    {
        if (false === $this->has($name)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown %s "%s", known %s nodes: "%s"',
                $this->context,
                $name,
                $this->context,
                implode('", "', array_keys($this->reflections))
            ));
        }

        return $this->reflections[$name];
    }

    public function all()
    {
        return $this->reflections;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->reflections);
    }
}
