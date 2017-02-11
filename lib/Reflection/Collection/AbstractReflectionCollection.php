<?php

namespace DTL\WorseReflection\Reflection\Collection;

use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\SourceContext;
use PhpParser\Node;

abstract class AbstractReflectionCollection implements \IteratorAggregate
{
    private $reflector;
    private $sourceContext;
    private $indexedNodes = [];
    private $loadedReflections = [];
    private $context;

    public function __construct(
        string $context,
        Reflector $reflector,
        SourceContext $sourceContext,
        array $nodes
    )
    {
        $this->context = $context;
        $this->reflector = $reflector;
        $this->sourceContext = $sourceContext;

        foreach ($nodes as $node) {
            $this->indexedNodes[(string) $node->name] = $node;
        }
    }

    public function has($name): bool
    {
        return isset($this->indexedNodes[$name]);
    }

    public function get(string $name)
    {
        if (isset($this->loadedReflections[$name])) {
            return $this->loadedReflections[$name];
        }

        if (false === $this->has($name)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown %s "%s", known %s nodes: "%s"',
                $this->context,
                $name,
                $this->context,
                implode('", "', array_keys($this->all()))
            ));
        }

        $this->loadedReflections[$name] = $this->createReflectionElement($this->reflector, $this->sourceContext, $this->indexedNodes[$name]);

        return $this->loadedReflections[$name];
    }

    public function all()
    {
        $methods = [];
        foreach (array_keys($this->indexedNodes) as $methodName) {
            $methods[] = $this->get($methodName);
        }

        return $methods;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->all());
    }

    abstract protected function createReflectionElement(Reflector $reflector, SourceContext $sourceContext, Node $node);
}

